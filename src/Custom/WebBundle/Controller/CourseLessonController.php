<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\StringToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;

class CourseLessonController extends BaseController
{
	public function previewAction(Request $request,$courseId,$lessonId)
	{
		$course = $this->getCourseService()->getCourse($courseId);
		if(empty($lessonId)){
			$lessons=$this->getCourseService()->getCourseLessons($courseId);
			$lesson = empty($lessons[0]) ? array() : $lessons[0];
			$isModal = false;
		}else{
        	$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
			$isModal = true;
		}
        $user = $this->getCurrentUser();

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (!empty($course['status']) && $course['status'] == 'closed') {
            return $this->render('TopxiaWebBundle:CourseLesson:preview-notice-modal.html.twig',array('course' => $course));
        }

        if ($isModal && empty($lesson['free'])) {
            if (!$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
            return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $courseId), array('preview' => true));
        }else{
            $allowAnonymousPreview = $this->setting('course.allowAnonymousPreview', 1);
            if (empty($allowAnonymousPreview) && !$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
        }

        if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hlsvideo.view', array('data' => $lessonId, 'times' => 1, 'duration' => 3600));
                    $hlsKeyUrl = $this->generateUrl('course_lesson_hlskeyurl', array('courseId' => $lesson['courseId'], 'lessonId' => $lesson['id'], 'token' => $token['token']), true);
                    $headLeaderInfo = $this->getHeadLeaderInfo();
                    $hls = $client->generateHLSEncryptedListUrl($file['convertParams'], $file['metas2'], $hlsKeyUrl, $headLeaderInfo['headLeaders'], $headLeaderInfo['headLeaderHlsKeyUrl'], 3600);
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
            'isModal' => $isModal,
            'hlsUrl' => (isset($hls) and is_array($hls) and !empty($hls['url'])) ? $hls['url'] : '',
        ));
	}




	private function getCourseService()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}
}