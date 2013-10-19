<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoCloudClient;

class CourseLessonController extends BaseController
{

    public function previewAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (empty($lesson['free'])) {
            return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $courseId), array('preview' => true));
        }

        return $this->render('TopxiaWebBundle:CourseLesson:preview-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    public function showAction(Request $request, $courseId, $lessonId)
    {
    	$course = $this->getCourseService()->tryTakeCourse($courseId);
    	$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $json = array();
        $json['number'] = $lesson['number'];

        $chapter = empty($lesson['chapterId']) ? null : $this->getCourseService()->getChapter($course['id'], $lesson['chapterId']);
        $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];

        $json['title'] = $lesson['title'];
        $json['summary'] = $lesson['summary'];
        $json['type'] = $lesson['type'];
        $json['content'] = $lesson['content'];
        $json['status'] = $lesson['status'];
        $json['quizNum'] = $lesson['quizNum'];
        $json['materialNum'] = $lesson['materialNum'];
        $json['mediaId'] = $lesson['mediaId'];
        $json['mediaSource'] = $lesson['mediaSource'];

        if ($json['mediaSource'] == 'self') {
            $json['mediaUri'] = $this->generateUrl('course_lesson_media', array('courseId'=>$lesson['courseId'], 'lessonId'=> $lesson['id']));
        } else {
            $json['mediaUri'] = $lesson['mediaUri'];
        }

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

        $file = $this->getDiskService()->getFile($lesson['mediaId']);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['storage'] == 'cloud') {

            $key = null;
            if ($file['type'] == 'video') {
                if (empty($file['formats']) || !is_array($file['formats'])) {
                    throw $this->createNotFoundException();
                }
                $formats = $file['formats'];
                foreach (array('hd', 'shd', 'sd') as $type) {
                    if (!empty($formats[$type])) {
                        $key = $formats[$type]['key'];
                        break;
                    }
                }
            } else {
                $uri = $this->getDiskService()->parseFileUri($file['uri']);
                $key = $uri['key'];
            }

            if (empty($key)){
                throw $this->createNotFoundException();
            }

            $setting = $this->setting('storage');
            $client = new EdusohoCloudClient(
                $setting['cloud_api_server'],
                $setting['cloud_access_key'],
                $setting['cloud_secret_key']
            );

            $client->download($file['bucket'], $key);

        }

        $uri = $this->getDiskService()->parseFileUri($file['uri']);
        return $this->createLocalMediaResponse($uri);
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
        return $this->createJsonResponse(true);
    }

    public function learnCancelAction(Request $request, $courseId, $lessonId)
    {
        $this->getCourseService()->cancelLearnLesson($courseId, $lessonId);
        return $this->createJsonResponse(true);
    }

    private function createLocalMediaResponse($uri)
    {

        if (!file_exists($uri['fullpath'])) {
            return $this->createNotFoundException();
        }

        // var_dump($uri);exit();

        $response = BinaryFileResponse::create($uri['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'lesson.mp4',
            iconv('UTF-8', 'ASCII//TRANSLIT', 'lesson.mp4')
        );

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

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}