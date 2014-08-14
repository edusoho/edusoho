<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\CloudClientFactory;

class CourseLessonController extends BaseController
{

    public function previewAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $user = $this->getCurrentUser();

        if (empty($this->setting('course.allowAnonymousPreview', 1)) && !$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (!empty($course['status']) && $course['status'] == 'closed') {
            return $this->render('TopxiaWebBundle:CourseLesson:preview-notice-modal.html.twig',array('course' => $course));
        }

        if (empty($lesson['free'])) {
            return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $courseId), array('preview' => true));
        }

        if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file['metas2']) && !empty($file['metas2']['hd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hlsvideo.view', array('times' => 1, 'duration' => 3600));
                    $hlsKeyUrl = $this->generateUrl('course_lesson_hlskeyurl', array('courseId' => $lesson['courseId'], 'lessonId' => $lesson['id'], 'token' => $token['token']), true);
                    $hls = $client->generateHLSEncryptedListUrl($file['convertParams'], $file['metas2'], $hlsKeyUrl, 3600);
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }

            }
        } else if ($lesson['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }

        } else if ($lesson['mediaSource'] == 'tudou'){
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        }
        return $this->render('TopxiaWebBundle:CourseLesson:preview-modal.html.twig', array(
            'user' => $user,
            'course' => $course,
            'lesson' => $lesson,
            'hlsUrl' => (isset($hls) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
        ));
    }

    public function hlskeyurlAction(Request $request, $courseId, $lessonId, $token)
    {
        if (!$this->getTokenService()->verifyToken('hlsvideo.view', $token)) {
            $fakeKey = $this->getTokenService()->makeFakeTokenString(16);
            return new Response($fakeKey);
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (empty($lesson['mediaId'])) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if (empty($file['convertParams']['hlsKey'])) {
            throw $this->createNotFoundException();
        }

        // if (!$lesson['free']) {
        //     list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        // }

        return new Response($file['convertParams']['hlsKey']);
    }

    public function showAction(Request $request, $courseId, $lessonId)
    {
    	list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

    	$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $json = array();
        $json['number'] = $lesson['number'];

        $chapter = empty($lesson['chapterId']) ? null : $this->getCourseService()->getChapter($course['id'], $lesson['chapterId']);
        if ($chapter['type'] == 'unit') {
            $unit = $chapter;
            $json['unitNumber'] = $unit['number'];

            $chapter = $this->getCourseService()->getChapter($course['id'], $unit['parentId']);
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];

        } else {
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];
            $json['unitNumber'] = 0;
        }

        $json['title'] = $lesson['title'];
        $json['summary'] = $lesson['summary'];
        $json['type'] = $lesson['type'];
        $json['content'] = $lesson['content'];
        $json['status'] = $lesson['status'];
        $json['quizNum'] = $lesson['quizNum'];
        $json['materialNum'] = $lesson['materialNum'];
        $json['mediaId'] = $lesson['mediaId'];
        $json['mediaSource'] = $lesson['mediaSource'];
        $json['startTimeFormat'] = date("m-d H:i",$lesson['startTime']);
        $json['endTimeFormat'] = date("H:i",$lesson['endTime']);
        $json['startTime'] = $lesson['startTime'];
        $json['endTime'] = $lesson['endTime'];
        $json['id'] = $lesson['id'];
        $json['courseId'] = $lesson['courseId'];

        if ($json['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            if (!empty($file)) {
                if ($file['storage'] == 'cloud') {
                    $factory = new CloudClientFactory();
                    $client = $factory->createClient();

                    $json['mediaConvertStatus'] = $file['convertStatus'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['hd']['key'])) {
                        if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                            $token = $this->getTokenService()->makeToken('hlsvideo.view', array('times' => 1, 'duration' => 3600));
                            $hlsKeyUrl = $this->generateUrl('course_lesson_hlskeyurl', array('courseId' => $lesson['courseId'], 'lessonId' => $lesson['id'], 'token' => $token['token']), true);
                            $url = $client->generateHLSEncryptedListUrl($file['convertParams'], $file['metas2'], $hlsKeyUrl, 3600);
                        } else {
                            $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                        }
                        $json['mediaHLSUri'] = $url['url'];
                    } else if ($file['type'] == 'ppt') {
                        $json['mediaUri'] = $this->generateUrl('course_lesson_ppt', array('courseId'=>$course['id'], 'lessonId' => $lesson['id']));
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
                            $url = $client->generateFileUrl($client->getBucket(), $key, 3600);
                            $json['mediaUri'] = $url['url'];
                        } else {
                            $json['mediaUri'] = '';
                        }

                    }
                } else {
                    $json['mediaUri'] = $this->generateUrl('course_lesson_media', array('courseId'=>$course['id'], 'lessonId' => $lesson['id']));
                }
            } else {
                $json['mediaUri'] = '';
                if ($lesson['type'] == 'video') {
                    $json['mediaError'] = '抱歉，视频文件不存在，暂时无法学习。';
                } else if ($lesson['type'] == 'audio') {
                    $json['mediaError'] = '抱歉，音频文件不存在，暂时无法学习。';
                } else if ($lesson['type'] == 'ppt') {
                    $json['mediaError'] = '抱歉，PPT文件不存在，暂时无法学习。';
                }
            }
        } else if ($json['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $json['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
                $json['mediaSource'] = 'iframe';
            } else {
                $json['mediaUri'] = $lesson['mediaUri'];
            }

        } else if ($json['mediaSource'] == 'tudou'){
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $json['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $json['mediaSource'] = 'iframe';
            } else {
                $json['mediaUri'] = $lesson['mediaUri'];
            }
        } else {
            $json['mediaUri'] = $lesson['mediaUri'];
        }

        $json['canLearn'] = $this->getCourseService()->canLearnLesson($lesson['courseId'], $lesson['id']);

    	return $this->createJsonResponse($json);
    }

    public function mediaAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);  
        if (empty($lesson) || empty($lesson['mediaId']) || ($lesson['mediaSource'] != 'self') ) {
            throw $this->createNotFoundException();
        }

        if (!$lesson['free']) {
            $this->getCourseService()->tryTakeCourse($courseId);
        }

        return $this->fileAction($request, $lesson['mediaId']);
    }

    public function mediaDownloadAction(Request $request, $courseId, $lessonId)
    {
        if (!$this->setting('course.student_download_media')) {
            return $this->createMessageResponse('未开启课时音视频下载。');
        }
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);  
        if (empty($lesson) || empty($lesson['mediaId']) || ($lesson['mediaSource'] != 'self') ) {
            throw $this->createNotFoundException();
        }

        $this->getCourseService()->tryTakeCourse($courseId);

        return $this->fileAction($request, $lesson['mediaId'], true);
    }

    public function pptAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if ($lesson['type'] != 'ppt' or empty($lesson['mediaId'])) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['convertStatus'] != 'success') {
            if ($file['convertStatus'] == 'error') {
                $url = $this->generateUrl('course_manage_files', array('id' => $courseId));
                $message = sprintf('PPT文档转换失败，请到课程<a href="%s" target="_blank">文件管理</a>中，重新转换。', $url);
                return $this->createJsonResponse(array(
                    'error' => array('code' => 'error', 'message' => $message),
                ));
            } else {
                return $this->createJsonResponse(array(
                    'error' => array('code' => 'processing', 'message' => 'PPT文档还在转换中，还不能查看，请稍等。'),
                ));
            }
        }

        $factory = new CloudClientFactory();
        $client = $factory->createClient();

        $result = $client->pptImages($file['metas2']['imagePrefix'], $file['metas2']['length']. '');

        return $this->createJsonResponse($result);
    }

    public function fileAction(Request $request, $fileId, $isDownload = false)
    {
        $file = $this->getUploadFileService()->getFile($fileId);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['storage'] == 'cloud') {
            if ($isDownload) {
                $key = $file['hashId'];
            } else {
                if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                    $key = $file['metas']['hd']['key'];
                } else {
                    $key = $file['hashId'];
                }
            }
            if (empty($key)){
                throw $this->createNotFoundException();
            }

            $factory = new CloudClientFactory();
            $client = $factory->createClient();

            if ($isDownload) {
                $client->download($client->getBucket(), $key, 3600, $file['filename']);
            } else {
                $client->download($client->getBucket(), $key);
            }
        }

        return $this->createLocalMediaResponse($request, $file, $isDownload);
    }

    public function learnStatusAction(Request $request, $courseId, $lessonId)
    {
        $user = $this->getCurrentUser();
        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $courseId, $lessonId);
        return $this->createJsonResponse(array('status' => $status ? : 'unstart'));
    }

    public function learnStartAction(Request $request, $courseId, $lessonId)
    {
        $result = $this->getCourseService()->startLearnLesson($courseId, $lessonId);
        return $this->createJsonResponse($result);
    }

    public function learnFinishAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->finishLearnLesson($courseId, $lessonId);

        $user = $this->getCurrentUser();
        $member = $this->getCourseService()->getCourseMember($courseId, $user['id']);

        $response = array(
            'learnedNum' => empty($member['learnedNum']) ? 0 : $member['learnedNum'],
            'isLearned' => empty($member['isLearned']) ? 0 : $member['isLearned'],
        );

        return $this->createJsonResponse($response);
    }

    public function learnCancelAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->cancelLearnLesson($courseId, $lessonId);
        return $this->createJsonResponse(true);
    }

    private function createLocalMediaResponse(Request $request, $file, $isDownload = false)
    {
        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $file['filename'] = urlencode($file['filename']);
        if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        } else {
            $response->headers->set('Content-Disposition', "attachment; filename*=UTF-8''".$file['filename']);
        }

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);
        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

    private function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}