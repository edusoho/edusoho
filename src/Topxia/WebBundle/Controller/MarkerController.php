<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    //新增弹题
    public function addQuestionMarkerAction(Request $request)
    {
        $data = $request->request->all();

        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $question           = $this->getQuestionService()->getQuestion($data['questionId']);

        if (empty($question)) {
            return $this->createMessageResponse('error', '该题目不存在!');
        }

        if (!isset($data['markerId'])) {
            $result = $this->getMarkerService()->addMarker($data['mediaId'], $data);
            return $this->createJsonResponse($result);
        } else {
            $marker = $this->getMarkerService()->getMarker($data['markerId']);

            if (!empty($marker)) {
                $questionmarker = $this->getQuestionMarkerService()->addQuestionMarker($data['qusetionId'], $marker['id'], $data['seq']);
                return $this->createJsonResponse($questionmarker);
            } else {
                return $this->createJsonResponse(false);
            }
        }
    }

    //删除弹题
    public function deleteQuestionMarkerAction(Request $request)
    {
        $data               = $request->request->all();
        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $result             = $this->getQuestionMarkerService()->deleteQuestionMarker($data['questionId']);
        return $this->createJsonResponse($result);
    }

    //弹题排序
    public function sortQuestionMarkerAction(Request $request)
    {
        $data   = $request->request->all();
        $data   = isset($data['questionIds']) ? $data['questionIds'] : array();
        $result = $this->getQuestionMarkerService()->sortQuestionMarkers($data);
        return $this->createJsonResponse($result);
    }

    //更新驻点时间
    public function updateMarkerAction(Request $request)
    {
        $data       = $request->request->all();
        $data['id'] = isset($data['id']) ? $data['id'] : 0;
        $fields     = array(
            'updatedTime' => time(),
            'second'      => isset($data['second']) ? $data['second'] : ""
        );
        $marker = $this->getMarkerService()->updateMarker($data['id'], $fields);
        return $this->createJsonResponse($marker);
    }

    //获取当前驻点弹题
    public function showQuestionMarkerAction(Request $request)
    {
        $data             = $request->request->all();
        $data['markerId'] = isset($data['markerId']) ? $data['markerId'] : 0;
        $questionmarkers  = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($data['markerId']);
        return $this->createJsonResponse($questionmarkers);
    }

    public function questionAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $conditions                  = $request->query->all();
        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $conditions, $course);
        return $this->render('TopxiaWebBundle:Marker:question.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'questions'     => $questions,
            'paginator'     => $paginator,
            'targetChoices' => $this->getQuestionTargetChoices($course, $lesson)
        ));
    }

    public function searchAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $conditions                  = $request->request->all();
        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $conditions, $course);
        return $this->render('TopxiaWebBundle:Marker:question.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'paginator'     => $paginator,
            'questions'     => $questions,
            'targetChoices' => $this->getQuestionTargetChoices($course, $lesson)
        ));
    }

    protected function getQuestionTargetChoices($course, $lesson)
    {
        $lessons                                                  = $this->getCourseService()->getCourseLessons($course['id']);
        $choices                                                  = array();
        $choices["course-{$course['id']}"]                        = '本课程';
        $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        return $choices;
    }

    protected function getPaginatorAndQuestion($request, $conditions, $course)
    {
        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        $conditions['parentId'] = 0;
        $conditions['types']    = array('determine', 'single_choice', 'uncertain_choice');
        $orderBy                = array('createdTime', 'DESC');

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchQuestionsCount($conditions),
            12
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $questions);
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
    }

    protected function getQuestionMarkerResultService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerResultService');
    }
}
