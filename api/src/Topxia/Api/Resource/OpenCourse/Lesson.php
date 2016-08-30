<?php

namespace Topxia\Api\Resource\OpenCourse;

use Topxia\Api\Resource\BaseResource;
use Topxia\Service\File\Impl\UploadFileServiceImpl;
use Topxia\Service\Util\CloudClientFactory;

class Lesson extends BaseResource
{
    public function filter($lesson)
    {
        $lesson['createdTime'] = date('c', $lesson['createdTime']);
        $lesson['updatedTime'] = date('c', $lesson['updatedTime']);

        $lesson['startTime'] = empty($lesson['startTime']) ? '' : date('c', $lesson['startTime']);
        $lesson['endTime']   = empty($lesson['endTime']) ? '' :date('c', $lesson['endTime']);

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
        $template          = $this->render('course/lesson-text-content.html.twig', array(
            'content' => $lesson['content']
        ));
        $lesson['content'] = $template;

        return $lesson;
    }

    protected function getVideoLesson($lesson)
    {
        $line = empty($lesson['hlsLine']) ? '' : $lesson['hlsLine'];

        $mediaId     = $lesson['mediaId'];
        $mediaSource = $lesson['mediaSource'];
        $mediaUri    = $lesson['mediaUri'];

        if ($mediaSource == 'self') {

            $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

            if (!empty($file)) {
                $lesson['mediaStorage'] = $file['storage'];
                if ($file['storage'] == 'cloud') {
                    $factory = new CloudClientFactory();
                    $client  = $factory->createClient();

                    $lesson['mediaConvertStatus'] = $file['convertStatus'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                            $headLeaderInfo = $this->getHeadLeaderInfo();

                            if ($headLeaderInfo) {
                                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                    'data'     => array(
                                        'id'      => $headLeaderInfo['id'],
                                        'fromApi' => true
                                    ),
                                    'times'    => 2,
                                    'duration' => 3600
                                ));

                                $headUrl = array(
                                    'url' => $this->getHttpHost() . "/hls/{$headLeaderInfo['id']}/playlist/{$token['token']}.m3u8?format=json&line=" . $line
                                );

                                $lesson['headUrl'] = $headUrl['url'];
                            }

                            $token = $this->getTokenService()->makeToken('hls.playlist', array(
                                'data'     => array(
                                    'id'      => $file['id'],
                                    'fromApi' => true
                                ),
                                'times'    => 2,
                                'duration' => 3600
                            ));

                            $url = array(
                                'url' => $this->getHttpHost() . "/hls/{$file['id']}/playlist/{$token['token']}.m3u8?format=json&line=" . $line
                            );
                        } else {
                            $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                        }

                        $lesson['mediaUri'] = (isset($url) && is_array($url) && !empty($url['url'])) ? $url['url'] : '';
                    } else {
                        if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                            $key = $file['metas']['hd']['key'];
                        } else {
                            if ($file['type'] == 'video') {
                                $key = null;
                            } else {
                                $key = $file['hashId'];
                            }
                        }

                        if ($key) {
                            $url                = $client->generateFileUrl($client->getBucket(), $key, 3600);
                            $lesson['mediaUri'] = isset($url["url"]) ? $url['url'] : "";
                        } else {
                            $lesson['mediaUri'] = '';
                        }
                    }
                } else {
                    $token              = $this->getTokenService()->makeToken('local.media', array(
                        'data'     => array(
                            'id' => $file['id']
                        ),
                        'duration' => 3600,
                        'userId'   => 0
                    ));
                    $lesson['mediaUri'] = $this->getHttpHost() . "/player/{$file['id']}/file/{$token['token']}";
                }
            } else {
                $lesson['mediaUri'] = '';
            }
        } elseif ($mediaSource == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
            } else {
                $lesson['mediaUri'] = '';
            }
        } elseif ($mediaSource == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
            } else {
                $lesson['mediaUri'] = '';
            }
        } else {
            $lesson['mediaUri'] = $mediaUri;
        }

        return $lesson;
    }

    protected function getHeadLeaderInfo()
    {
        $storage = $this->getSettingService()->get("storage");

        if (!empty($storage) && array_key_exists("video_header", $storage) && $storage["video_header"]) {
            $file = $this->getUploadFileService()->getFileByTargetType('headLeader');
            return $file;
        }

        return false;
    }

    /**
     * @return UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }
}
