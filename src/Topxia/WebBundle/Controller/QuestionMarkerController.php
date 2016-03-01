<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class QuestionMarkerController extends BaseController
{
    public function sortQuestionAction(Request $Request, $markerId)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $ids  = $data['ids'];
            $this->getQuestionMarkerService()->sortQuestionMarkers($ids);
            return $this->createJsonResponse(true);
        }

        $marker = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($markerId);
        //返回twig为某一个驻点的所有问题
        return $this->render('TopxiaWebBundle:Marker:question-marker-modal.html.twig', array(
            'marker' => $marker
        ));
    }

    //删除弹题
    public function deleteQuestionMarkerAction(Request $request)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data               = $request->request->all();
        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $result             = $this->getQuestionMarkerService()->deleteQuestionMarker($data['questionId']);
        return $this->createJsonResponse($result);
    }

    //弹题排序
    public function sortQuestionMarkerAction(Request $request)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data   = $request->request->all();
        $data   = isset($data['questionIds']) ? $data['questionIds'] : array();
        $result = $this->getQuestionMarkerService()->sortQuestionMarkers($data);
        return $this->createJsonResponse($result);
    }

    //新增弹题
    public function addQuestionMarkerAction(Request $request, $courseId, $lessonId)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();

        $lesson = $this->getCourseService()->getLesson($lessonId);

        if (empty($lesson)) {
            return $this->createMessageResponse('error', '该课时不存在!');
        }

        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $question           = $this->getQuestionService()->getQuestion($data['questionId']);

        if (empty($question)) {
            return $this->createMessageResponse('error', '该题目不存在!');
        }

        if (empty($data['markerId'])) {
            $result = $this->getMarkerService()->addMarker($lesson['mediaId'], $data);
            return $this->createJsonResponse($result);
        } else {
            $marker = $this->getMarkerService()->getMarker($data['markerId']);

            if (!empty($marker)) {
                $questionmarker = $this->getQuestionMarkerService()->addQuestionMarker($data['questionId'], $marker['id'], $data['seq']);
                return $this->createJsonResponse($questionmarker);
            } else {
                return $this->createJsonResponse(false);
            }
        }
    }

    //获取驻点弹题
    public function showMarkerQuestionAction(Request $request, $markerId)
    {
        $user      = $this->getUserService()->getCurrentUser();
        $question  = array();
        $data      = $request->query->all();
        $questions = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($markerId);

        if ($this->getMarkerService()->isFinishMarker($user['id'], $markerId)) {
            if (isset($data['questionId'])) {
                $question   = $this->getQuestionMarkerService()->getQuestionMarker($data['questionId']);
                $conditions = array(
                    'seq'      => ++$question['seq'],
                    'markerId' => $markerId
                );
                $question = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('seq', 'ASC'), 0, 1);

                if (!empty($question)) {
                    $question = $question['0'];
                }
            } else {
                $conditions = array(
                    'seq'      => 1,
                    'markerId' => $markerId
                );
                $question = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('seq', 'ASC'), 0, 1);
                $question = $question[0];
            }
        } else {
            foreach ($questions as $key => $value) {
                $questionResult = $this->getQuestionMarkerResultService()->findByUserIdAndQuestionMarkerId($user['id'], $value['id']);

                if (empty($questionResult)) {
                    $question = $value;
                    break;
                }
            }
        }

        return $this->render('TopxiaWebBundle:Marker:question-modal.html.twig', array(
            'markerId' => $markerId,
            'question' => $question,
            'lessonId' => $data['lessonId']
        ));
    }

    public function doNextTestAction(Request $request)
    {
        $data                 = $request->query->all();
        $data['markerId']     = isset($data['markerId']) ? $data['markerId'] : 0;
        $data['questionId']   = isset($data['questionId']) ? $data['questionId'] : 0;
        $data['answer']       = isset($data['answer']) ? $data['answer'] : null;
        $data['type']         = isset($data['type']) ? $data['type'] : null;
        $user                 = $this->getUserService()->getCurrentUser();
        $questionMarkerResult = $this->getQuestionMarkerResultService()->finishCurrentQuestion($data['markerId'], $user['id'], $data['questionId'], $data['answer'], $data['type'], $data['lessonId']);

        $data = array(
            'markerId'               => $data['markerId'],
            'questionMarkerResultId' => $questionMarkerResult['id']
        );
        return $this->createJsonResponse($data);
    }

    public function showQuestionAnswerAction(Request $request, $questionId)
    {
        $data                 = $request->query->all();
        $user                 = $this->getUserService()->getCurrentUser();
        $questionMarker       = $this->getQuestionMarkerService()->getQuestionMarker($questionId);
        $questionMarkerResult = $this->getQuestionMarkerResultService()->getQuestionMarkerResult($data['questionMarkerResultId']);
        $conditions           = array(
            'markerId' => $data['markerId']
        );
        $count                 = $this->getQuestionMarkerService()->searchQuestionMarkersCount($conditions);
        $questionMarker['seq'] = isset($questionMarker['seq']) ? $questionMarker['seq'] : 1;
        $progress              = array(
            'seq'     => $questionMarker['seq'],
            'count'   => $count,
            'percent' => floor($questionMarker['seq'] / $count * 100)
        );
        $compelete = $progress['percent'] == 100 ? true : false;

        return $this->render('TopxiaWebBundle:Marker:answer.html.twig', array(
            'markerId'   => $data['markerId'],
            'question'   => $questionMarker,
            'answer'     => $questionMarker['answer'],
            'selfAnswer' => unserialize($questionMarkerResult['answer']),
            'status'     => $questionMarkerResult['status'],
            'progress'   => $progress,
            'compelete'  => $compelete
        ));
    }

    public function questionAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        return $this->render('TopxiaWebBundle:Marker:question.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'targetChoices' => $this->getQuestionTargetChoices($course, $lesson)
        ));
    }

    public function searchAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $conditions = $request->request->all();

        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $conditions, $course, $lesson);

        return $this->render('TopxiaWebBundle:Marker:question-tr.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'paginator'     => $paginator,
            'questions'     => $questions,
            'targetChoices' => $this->getQuestionTargetChoices($course)
        ));
    }

    protected function getQuestionTargetChoices($course)
    {
        $lessons                           = $this->getCourseService()->getCourseLessons($course['id']);
        $choices                           = array();
        $choices["course-{$course['id']}"] = '本课程';

        foreach ($lessons as $lesson) {
            $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        }

        return $choices;
    }

    protected function getPaginatorAndQuestion($request, $conditions, $course, $lesson)
    {
        if (!isset($conditions['target']) || empty($conditions['target'])) {
            unset($conditions['target']);
            $conditions['targetPrefix'] = "course-{$course['id']}";
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        $conditions['parentId'] = 0;
        $conditions['types']    = array('determine', 'single_choice', 'uncertain_choice', 'fill', "choice");
        $orderBy                = array('createdTime', 'DESC');
        $paginator              = new Paginator(
            $request,
            $this->getQuestionService()->searchQuestionsCount($conditions),
            5
        );

        $paginator->setPageRange(4);

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $markerIds         = ArrayToolkit::column($this->getMarkerService()->findMarkersByMediaId($lesson['mediaId']), 'id');
        $questionMarkerIds = ArrayToolkit::column($this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds($markerIds), 'questionId');

        foreach ($questions as $key => $question) {
            $questions[$key]['exist'] = in_array($question['id'], $questionMarkerIds) ? true : false;
        }

        return array($paginator, $questions);
    }

    protected function tryManageQuestionMarker()
    {
        $user = $this->getUserService()->getCurrentUser();

        if ($this->getUserService()->hasAdminRoles($user['id'])) {
            return true;
        }

        if (in_array("ROLE_TEACHER", $user['roles'])) {
            return true;
        }

        return false;
    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
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

    protected function getQuestionMarkerResultService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerResultService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
