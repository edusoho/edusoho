<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;

class Lesson extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $lesson = $this->getCourseService()->getLesson($id);
        if (empty($lesson)) {
            return $this->error('not_courseId', "ID为#{$id}的课时不存在");
        }

        $courseSetting         = $this->getSettingService()->get('course');
        $allowAnonymousPreview = isset($courseSetting['allowAnonymousPreview']) ? $courseSetting['allowAnonymousPreview'] : 0;

        if (!$allowAnonymousPreview || ($allowAnonymousPreview && !$lesson['free'])) {
            $currentUser = $this->getCurrentUser();
            if (empty($currentUser) || !$currentUser->isLogin()) {
                return $this->error('not_login', "您尚未登录，不能查看该课时");
            } else {
                $member = $this->getCourseService()->getCourseMember($lesson['courseId'], $currentUser['id']);
                if (!$lesson['free'] && empty($member)) {
                    return $this->error('not_student', "你不是该课程学员，请加入学习");
                }
            }

            $this->setStartLesson($lesson['courseId'], $id);
        }

        if ($line = $request->query->get('line')) {
            $lesson['hlsLine'] = $line;
        }

        return $this->filter($lesson);
    }

    public function filter($lesson)
    {
        $lesson['createdTime'] = date('c', $lesson['createdTime']);
        $lesson['updatedTime'] = date('c', $lesson['updatedTime']);

        switch ($lesson['type']) {
            case 'ppt':
                return $this->getPPTLesson($lesson);
            case 'audio':
                return $this->getVideoLesson($lesson);
            case 'video':
                return $this->getVideoLesson($lesson);
            case 'testpaper':
                return $this->getTestpaperLesson($lesson);
            case 'document':
                return $this->getDocumentLesson($lesson);
            default:
                return $this->getTextLesson($lesson);
        }
    }

    protected function getPPTLesson($lesson)
    {
        $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

        if (empty($file)) {
            return $this->error('not_ppt', "文件不存在");
        }

        if ($file['convertStatus'] == 'error') {
            return $this->error('not_ppt', 'PPT文档转换失败，请到课程文件管理中，重新转换');
        }

        if ($file['convertStatus'] != 'success') {
            return $this->error('not_ppt', 'PPT文档还在转换中，还不能查看，请稍等');
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        $ppt = $client->pptImages($file['metas2']['imagePrefix'], $file['metas2']['length'].'');

        if (isset($ppt["error"])) {
            $ppt = array();
        }

        $lesson['content'] = array(
            'resource' => $ppt
        );

        return $lesson;
    }

    protected function getDocumentLesson($lesson)
    {
        $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);
        if (empty($file)) {
            return $this->error('not_document', "文件不存在");
        }

        if ($file['convertStatus'] == 'error') {
            return $this->error('not_document', '文档转换失败，请联系管理员');
        }

        if ($file['convertStatus'] != 'success') {
            return $this->error('not_document', '文档还在转换中，还不能查看，请稍等');
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        $metas2 = $file['metas2'];
        $url    = $client->generateFileUrl($client->getBucket(), $metas2['pdf']['key'], 3600);
        $pdfUri = $url['url'];
        // $url    = $client->generateFileUrl($client->getBucket(), $metas2['swf']['key'], 3600);
        // $swfUri = $url['url'];

        $lesson['content'] = array(
            'previewUrl' => 'http://opencdn.edusoho.net/pdf.js/v3/viewer.html#'.$pdfUri,
            'resource'   => $pdfUri
        );

        return $lesson;
    }

    protected function getTestpaperLesson($lesson)
    {
        $user      = $this->getCurrentUser();
        $testpaper = $this->getTestpaperService()->getTestpaper($lesson['mediaId']);
        if (empty($testpaper)) {
            return $this->error('error', '试卷不存在!');
        }

        $testResult        = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive($lesson['mediaId'], $user['id']);
        $lesson['content'] = array(
            'status'   => empty($testResult) ? 'nodo' : $testResult['status'],
            'resultId' => empty($testResult) ? 0 : $testResult['id']
        );

        return $lesson;
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
                                    'url' => $this->getHttpHost()."/hls/{$headLeaderInfo['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line
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
                                'url' => $this->getHttpHost()."/hls/{$file['id']}/playlist/{$token['token']}.m3u8?format=json&line=".$line
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
                    $token = $this->getTokenService()->makeToken('local.media', array(
                        'data'     => array(
                            'id' => $file['id']
                        ),
                        'duration' => 3600,
                        'userId'   => 0
                    ));
                    $lesson['mediaUri'] = $this->getHttpHost()."/player/{$file['id']}/file/{$token['token']}";
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

    protected function setStartLesson($courseId, $lessonId)
    {
        $user = $this->getCurrentUser();
        if ($user && $this->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $this->getCourseService()->startLearnLesson($courseId, $lessonId);
        }
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
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
