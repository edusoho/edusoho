<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClient;

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

        if (!empty($lesson['media'])) {
            $json['media'] = $this->convertMedia($lesson);
        }

    	return $this->createJsonResponse($json);
    }

    public function mediaAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);  
        if (empty($lesson) || empty($lesson['media']) || ($lesson['media']['source'] != 'self') ) {
            throw $this->createNotFoundException();
        }

        if (!$lesson['free']) {
            $this->getCourseService()->tryTakeCourse($courseId);
        }

        $uri = $this->getDiskService()->parseFileUri($lesson['media']['files'][0]['url']);

        if ($uri['type'] == 'cloud') {
            $user = $this->getCurrentUser();

            $setting = $this->setting('storage');
            $client = new CloudClient(
                $setting['cloud_access_key'],
                $setting['cloud_secret_key'],
                $setting['cloud_bucket'],
                $setting['cloud_bucket_domain'],
                $setting['cloud_mac_index'],
                $setting['cloud_mac_key']
            );

            $url = $client->getDownloadUrl($uri['key']);
            $cookieToken = $client->generateDownloadCookieToken($url, time() + 3600);
            setrawcookie('qiniuToken', $cookieToken, 0, '/', 'edusoho.net', false, true);

            return $this->redirect($url);
        }

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

    private function convertMedia($lesson)
    {
        $media = $lesson['media'];
        if ($media['source'] == 'self') {
            foreach ($media['files'] as $index => $file) {
                $media['files'][$index]['url'] = $this->generateUrl('course_lesson_media', array('courseId'=>$lesson['courseId'], 'lessonId'=> $lesson['id']));
            }
        }
        return $media;
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