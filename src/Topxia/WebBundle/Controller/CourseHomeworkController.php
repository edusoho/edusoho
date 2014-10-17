<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;

class CourseHomeworkController extends BaseController
{
    public function startDoAction(Request $request, $courseId, $homeworkId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $result = $this->getHomeworkService()->startHomework($homeworkId);
        return $this->redirect($this->generateUrl('course_homework_do', 
            array(
                'courseId' => $result['courseId'],
                'homeworkId' => $result['homeworkId'],
                'resultId' => $result['id'],
            ))
        );
    }

    public function doAction(Request $request, $courseId, $homeworkId, $resultId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        return $this->render('TopxiaWebBundle:CourseHomework:do.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'doing'
        ));
    }

    public function submitAction(Request $request,$courseId,$homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->submitHomework($homeworkId,$data);
            if (!empty($res) && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $courseId,'lessonId' => $res['lessonId']));
            }
        }
    }
    
    public function saveAction(Request $request ,$courseId,$homeworkId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $homework = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->saveHomework($homeworkId,$homework);
            if ($res && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $courseId,'lessonId' => $res['lessonId']));
            }
        }
    }

    public function continueAction(Request $request ,$courseId,$homeworkId, $resultId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$this->getCurrentuser()->id);
        return $this->render('TopxiaWebBundle:CourseHomework:continue.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'continue'
        ));
    }

    public function resultAction(Request $request, $courseId, $homeworkId, $resultId ,$userId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);

        return $this->render('TopxiaWebBundle:CourseHomework:result.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'finished'
        ));
    }

    public function checkAction(Request $request, $courseId, $homeworkId,$userId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        if ($request->getMethod() == 'POST') {

             $checkHomeworkData = $request->request->all();
             $this->getHomeworkService()->checkHomework($homeworkId,$userId,$checkHomeworkData['data']);

             return $this->createJsonResponse(array('status' => 'success'));
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'],$userId);
        return $this->render('TopxiaWebBundle:CourseHomework:check.html.twig', array(
            'homework' => $homework,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'userId' => $userId,
            'questionStatus' => 'reviewing'
        ));
    }

    public function previewAction(Request $request, $courseId, $homeworkId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        return $this->render('TopxiaWebBundle:CourseHomework:preview.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'previewing'
        ));   
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Course.HomeworkService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}