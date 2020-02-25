<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Component\MediaParser\ParserProxy;
use Biz\File\Service\UploadFileService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\TokenService;

class OpenCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw OpenCourseException::NOTFOUND_OPENCOURSE();
        }

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($courseId);

        if (empty($lessons)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }

        $lesson = $lessons[0];
        if ($line = $request->query->get('line')) {
            $lesson['hlsLine'] = $line;
        }

        if ($lesson['replayStatus'] == 'videoGenerated') {
            $lesson = $this->getVideoLesson($request, $lesson);
        }

        $course = $this->getOpenCourseService()->waveCourse($courseId, 'hitNum', +1);
        $course['lesson'] = $lesson;

        return $course;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getOpenCourseService()->countLiveCourses($request->query->all());

        $courses = $this->getOpenCourseService()->searchAndSortLiveCourses($request->query->all(), $offset, $limit);

        $this->getOCUtil()->multiple($courses, array('userId', 'teacherIds'));

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    protected function getVideoLesson(ApiRequest $request, $lesson)
    {
        $line = empty($lesson['hlsLine']) ? '' : $lesson['hlsLine'];

        if ('self' == $lesson['mediaSource']) {
            $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

            if (!empty($file)) {
                $lesson['mediaStorage'] = $file['storage'];
                if ('cloud' == $file['storage']) {
                    $lesson['mediaConvertStatus'] = $file['convertStatus'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ('HLSEncryptedVideo' == $file['convertParams']['convertor'])) {
                            $headLeaderInfo = $this->getHeadLeaderInfo();

                            if ($headLeaderInfo) {
                                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                    'data' => array(
                                        'id' => $headLeaderInfo['id'],
                                        'fromApi' => true,
                                    ),
                                    'times' => 2,
                                    'duration' => 3600,
                                ));

                                $headUrl = array(
                                    'url' => $this->getHttpHost($request)."/hls/{$headLeaderInfo['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line,
                                );

                                $lesson['headUrl'] = $headUrl['url'];
                            }

                            $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                'data' => array(
                                    'id' => $file['id'],
                                    'fromApi' => true,
                                ),
                                'times' => 2,
                                'duration' => 3600,
                            ));

                            $url = array(
                                'url' => $this->getHttpHost($request)."/hls/{$file['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line,
                            );
                        } else {
                            return $this->error('404', '当前视频格式不能被播放！');
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
                            return $this->error('404', '当前视频格式不能被播放！');
                        } else {
                            $lesson['mediaUri'] = '';
                        }
                    }
                } else {
                    $token = $this->getTokenService()->makeToken('local.media', array(
                        'data' => array(
                            'id' => $file['id'],
                        ),
                        'duration' => 3600,
                        'userId' => 0,
                    ));
                    $lesson['mediaUri'] = $this->getHttpHost($request)."/player/{$file['id']}/file/{$token['token']}";
                }
            } else {
                $lesson['mediaUri'] = '';
            }
        } else {
            $proxy = new ParserProxy();
            $lesson = $proxy->prepareMediaUriForMobile($lesson, $request->getHttpRequest()->getScheme());
        }

        return $lesson;
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

    protected function getHttpHost(ApiRequest $request)
    {
        return $request->getHttpRequest()->getScheme()."://{$_SERVER['HTTP_HOST']}";
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
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
