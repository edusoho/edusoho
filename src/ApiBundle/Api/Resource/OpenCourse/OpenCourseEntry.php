<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\SettingToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\Player\PlayerException;
use Biz\System\Service\SettingService;
use Biz\User\Service\TokenService;

class OpenCourseEntry extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $lessonId)
    {
        $this->checkCourseLesson($courseId, $lessonId);

        if ($this->getLiveCourseService()->isLiveFinished($lessonId)) {
            return $this->entryReplay($request, $courseId, $lessonId);
        }

        return $this->entryLive($request, $courseId, $lessonId);
    }

    protected function entryLive(ApiRequest $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $result = $this->getLiveCourseService()->checkLessonStatus($lesson);

        if (!$result['result']) {
            return array('lesson' => $lesson, 'live' => array(), 'message' => $result['message']);
        }

        $params = array();

        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            $this->getOpenCourseService()->createMember(array(
                'courseId' => $courseId,
                'ip' => $request->getHttpRequest()->getClientIp(),
                'lastEnterTime' => time(),
            ));
        }

        $params['role'] = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);
        $params['id'] = $user->isLogin() ? $user['id'] : (int) ($this->getMillisecond()) * 1000 + rand(0, 999);
        $params['nickname'] = $user->isLogin() ? $user['nickname'] : '游客'.$this->getRandomString(8);
        $params['isLogin'] = $user->isLogin();
        $params['startTime'] = $lesson['startTime'];
        $params['endTime'] = $lesson['endTime'];

        $params['device'] = $request->query->get('device', DeviceToolkit::isMobileClient() ? 'mobile' : 'desktop');

        $liveTicket = CloudAPIFactory::create('leaf')->post("/liverooms/{$lesson['mediaId']}/tickets", $params);

        $liveTicket['ticket'] = CloudAPIFactory::create('leaf')->get("/liverooms/{$lesson['mediaId']}/tickets/{$liveTicket['no']}");

        return array('lesson' => $lesson, 'live' => $liveTicket);
    }

    protected function entryReplay(ApiRequest $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if ($lesson['replayStatus'] == 'videoGenerated') {
            $lesson = $this->getVideoPlayUrl($request, $lesson);

            return array('lesson' => $lesson);
        }

        $device = $request->query->get('device', DeviceToolkit::isMobileClient() ? 'mobile' : 'desktop');
        $replays = $this->getLiveReplayService()->findReplaysByCourseIdAndLessonId($courseId, $lessonId, 'liveOpen');

        if (!$replays) {
            return array('lesson' => $lesson, 'replay' => array(), 'message' => '未生成回放');
        }

        $visibleReplays = array_filter($replays, function ($replay) {
            return empty($replay['hidden']);
        });

        $visibleReplays = array_values($visibleReplays);

        $user = $this->getCurrentUser();

        $user['userId'] = $user->isLogin() ? $user['id'] : (int) ($this->getMillisecond()) * 1000 + rand(0, 999);
        $user['nickname'] = $user->isLogin() ? $user['nickname'] : '游客'.$this->getRandomString(8);

        $protocol = $request->getHttpRequest()->getScheme();
        $replays = array();

        foreach ($visibleReplays as $index => $visibleReplay) {
            $replays[] = CloudAPIFactory::create('root')->get("/lives/{$lesson['mediaId']}/replay", array(
                'replayId' => $visibleReplays[$index]['replayId'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'device' => $device,
                'protocol' => $protocol, ));
            $replays[$index]['title'] = $visibleReplay['title'];
        }

        return array('lesson' => $lesson, 'replay' => $replays);
    }

    protected function getVideoPlayUrl(ApiRequest $request, $lesson)
    {
        $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

        if (empty($file)) {
            throw UploadFileException::NOTFOUND_FILE();
        }

        if ($file['storage'] == 'local') {
            $token = $this->getTokenService()->makeToken('local.media', array(
                'data' => array(
                    'id' => $file['id'],
                ),
                'duration' => 3600,
                'userId' => 0,
            ));
            $lesson['mediaUri'] = $this->getHttpHost($request)."/player/{$file['id']}/file/{$token['token']}";

            return $lesson;
        }

        $lesson['mediaConvertStatus'] = $file['convertStatus'];

        $hlsEncryption = false;
        if (SettingToolkit::getSetting('storage.enable_hls_encryption_plus')) {
            $hlsEncryption = true;
        }

        if (isset($file['processAudioStatus']) && 'ok' == $file['processAudioStatus']) {
            if (!empty($file['audioMetas2']) && !empty($file['audioMetas2']['sd']['key'])) {
                $data = array(
                    'id' => $file['id'],
                    'fromApi' => !$hlsEncryption,
                );

                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                    'data' => $data,
                    'times' => 2,
                    'duration' => 3600,
                ));

                $audioUrl = array(
                    'url' => $this->getHttpHost($request)."/hls/{$file['id']}/audio/playlist/{$token['token']}.m3u8?format=json",
                );

                if (isset($audioUrl) && is_array($audioUrl) && !empty($audioUrl['url'])) {
                    $lesson['audioUri'] = $audioUrl['url'];
                }
            }
        }

        if (!empty($file['metas2']) && $this->isNotEmptyMetas2Contents($file['metas2'])) {
            if (isset($file['convertParams']['convertor']) && ('HLSEncryptedVideo' == $file['convertParams']['convertor'])) {
                $headLeaderInfo = $this->getHeadLeaderInfo();

                if ($headLeaderInfo) {
                    $token = $this->getTokenService()->makeToken('hls.playlist', array(
                        'data' => array(
                            'id' => $headLeaderInfo['id'],
                            'fromApi' => !$hlsEncryption,
                        ),
                        'times' => 2,
                        'duration' => 3600,
                    ));

                    $headUrl = array(
                        'url' => $this->getHttpHost($request)."/hls/{$headLeaderInfo['id']}/playlist/{$token['token']}.m3u8?format=json",
                    );

                    $lesson['headUrl'] = $headUrl['url'];
                    $lesson['headLength'] = $headLeaderInfo['length'];
                }

                $data = array(
                    'id' => $file['id'],
                    'fromApi' => !$hlsEncryption,
                );

                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                    'data' => $data,
                    'times' => 2,
                    'duration' => 3600,
                ));

                $url = array(
                    'url' => $this->getHttpHost($request)."/hls/{$file['id']}/playlist/{$token['token']}.m3u8?format=json",
                );
            } else {
                throw PlayerException::NOT_SUPPORT_TYPE();
            }

            $lesson['mediaUri'] = (isset($url) && is_array($url) && !empty($url['url'])) ? $url['url'] : '';
        } else {
            if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                $key = $file['metas']['hd']['key'];
            } else {
                if ('video' == $file['type']) {
                    $key = null;
                } else {
                    $key = $file['hashId'];
                }
            }

            if ($key) {
                throw PlayerException::NOT_SUPPORT_TYPE();
            }
        }

        return $lesson;
    }

    protected function getHttpHost(ApiRequest $request)
    {
        return $request->getHttpRequest()->getScheme()."://{$_SERVER['HTTP_HOST']}";
    }

    protected function checkCourseLesson($courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw OpenCourseException::NOTFOUND_OPENCOURSE();
        }

        if ('published' != $course['status']) {
            throw OpenCourseException::STATUS_INVALID();
        }

        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }

        if ('published' != $lesson['status']) {
            throw OpenCourseException::STATUS_INVALID();
        }
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());

        return (float) sprintf('%.0f', ((float) ($t1) + (float) ($t2)) * 1000);
    }

    protected function isNotEmptyMetas2Contents($metas)
    {
        foreach ($metas as $meta) {
            if (!empty($meta['key'])) {
                return true;
            }
        }

        return false;
    }

    protected function getHeadLeaderInfo()
    {
        $storage = $this->getSettingService()->get('storage');

        if (!empty($storage) && array_key_exists('video_header', $storage) && $storage['video_header']) {
            $file = $this->getUploadFileService()->getFileByTargetType('headLeader');

            return $file;
        }

        return false;
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return LiveCourseService
     */
    protected function getLiveCourseService()
    {
        return $this->getBiz()->service('OpenCourse:LiveCourseService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->service('Course:LiveReplayService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
