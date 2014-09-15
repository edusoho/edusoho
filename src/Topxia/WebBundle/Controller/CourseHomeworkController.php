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
            var_dump($data);
            exit();
            $data = !empty($data['data']) ? $data['data'] : array();
            $res = $this->getHomeworkService()->submitHomework($homeworkId,$data);
            if (!empty($res) && !empty($res['lessonId'])) {
               return $this->createJsonResponse(array('courseId' => $courseId,'lessonId' => $res['lessonId']));
            }
        }
    }
    
    public function resultAction(Request $request, $courseId, $homeworkId, $resultId)
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

        // $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);
        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkId($homework['id']);
        // var_dump($itemSetResult['items']);
        // exit();

        // foreach ($itemSetResult['items'] as $key => $itemResult) {

            // if (!empty($itemResult['seq'])) {
                // var_dump($itemResult['seq']);
            // } else {
                // file_put_contents('/tmp/indexRe', $key."@");

            // }
            // var_dump($itemSetResult['items'][7]);exi
        // }
        // exit();
        // $questionSet = $this->getQuestionService()->findQuestionsByIds($itemSetResult['questionIds']);
        return $this->render('TopxiaWebBundle:CourseHomework:result.html.twig', array(
            'homework' => $homework,
            // 'itemSet' => $itemSet,
            'itemSetResult' => $itemSetResult,
            // 'questionSet' => $questionSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'finished'
        ));
    }

    public function reviewAction(Request $request, $courseId, $homeworkId, $resultId)
    {
        
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