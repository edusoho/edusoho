<?php

namespace Topxia\Api\Resource\OpenCourse;

use Topxia\Api\Resource\BaseResource;
use Biz\File\Service\UploadFileService;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Component\MediaParser\ParserProxy;

class Lesson extends BaseResource
{
    public function filter($lesson)
    {
        $lesson['createdTime'] = date('c', $lesson['createdTime']);
        $lesson['updatedTime'] = date('c', $lesson['updatedTime']);

        $lesson['startTime'] = empty($lesson['startTime']) ? '' : date('c', $lesson['startTime']);
        $lesson['endTime'] = empty($lesson['endTime']) ? '' : date('c', $lesson['endTime']);

        unset($lesson['free']);
        unset($lesson['quizNum']);
        unset($lesson['learnedNum']);
        unset($lesson['viewedNum']);
        unset($lesson['giveCredit']);
        unset($lesson['requireCredit']);
        unset($lesson['homeworkId']);
        unset($lesson['exerciseId']);
        unset($lesson['suggestHours']);
        unset($lesson['testMode']);
        unset($lesson['testStartTime']);

        switch ($lesson['type']) {
            case 'video':
                return $this->getVideoLesson($lesson);
            default:
                return $this->getTextLesson($lesson);
        }
    }

    private function getTextLesson($lesson)
    {
        $lesson['content'] = $this->filterHtml($lesson['content']);
        $template = $this->render('course/lesson-text-content.html.twig', array(
            'content' => $lesson['content'],
        ));
        $lesson['content'] = $template;

        return $lesson;
    }

    protected function getVideoLesson($lesson)
    {
        $line = empty($lesson['hlsLine']) ? '' : $lesson['hlsLine'];

        $mediaId = $lesson['mediaId'];
        $mediaSource = $lesson['mediaSource'];
        $mediaUri = $lesson['mediaUri'];

        if ('self' == $mediaSource) {
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
                                    'url' => $this->getHttpHost()."/hls/{$headLeaderInfo['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line,
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
                                'url' => $this->getHttpHost()."/hls/{$file['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line,
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
                    $lesson['mediaUri'] = $this->getHttpHost()."/player/{$file['id']}/file/{$token['token']}";
                }
            } else {
                $lesson['mediaUri'] = '';
            }
        } else {
            $proxy = new ParserProxy();
            $lesson = $proxy->prepareMediaUriForMobile($lesson, $this->getSchema());
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File:UploadFileService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }
}
