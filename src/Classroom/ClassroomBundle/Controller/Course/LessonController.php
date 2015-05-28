<?php
namespace Classroom\ClassroomBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\WebBundle\Controller\BaseController;

class LessonController extends BaseController
{

    public function previewAction(Request $request, $classroomId, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $user = $this->getCurrentUser();

        if (empty($lesson)) {
            throw $this->createNotFoundException();
        }

        if (!empty($course['status']) && $course['status'] == 'closed') {
            return $this->render('TopxiaWebBundle:CourseLesson:preview-notice-modal.html.twig', array('course' => $course));
        }

        if (empty($lesson['free'])) {
            if (!$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }

            return $this->forward('TopxiaWebBundle:CourseOrder:buy', array('id' => $courseId), array('preview' => true));
        } else {
            $allowAnonymousPreview = $this->setting('course.allowAnonymousPreview', 1);
            if (empty($allowAnonymousPreview) && !$user->isLogin()) {
                throw $this->createAccessDeniedException();
            }
        }

        $hasVideoWatermarkEmbedded = 0;
        if ($lesson['type'] == 'video' && $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client = $factory->createClient();
                $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hls.playlist', array('data' => $file['id'], 'times' => 3, 'duration' => 3600));
                    $hls = array(
                        'url' => $this->generateUrl('hls_playlist', array(
                            'id' => $file['id'],
                            'token' => $token['token'],
                            'line' => $request->query->get('line'),
                        ), true),
                    );
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }
            }
            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $hasVideoWatermarkEmbedded = 1;
            }
        } elseif ($lesson['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $lesson['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        } elseif ($lesson['mediaSource'] == 'tudou') {
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
            'hasVideoWatermarkEmbedded' => $hasVideoWatermarkEmbedded,
            'hlsUrl' => (isset($hls) && is_array($hls) && !empty($hls['url'])) ? $hls['url'] : '',
        ));
    }

    public function listAction(Request $request, $classroomId, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroomId, $user['id']) : null;
        return $this->render('ClassroomBundle:Classroom/Course:lessons-list.html.twig', array(
            'classroomId' => $classroomId,
            'courseId' => $courseId,
            'member' => $member
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
