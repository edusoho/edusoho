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

        $this->getMarkerService()->canManageMarker($lesson['userId']);

        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    //驻点合并
    public function mergeAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $data = $request->request->all();

        if (empty($data['sourceMarkerId']) || empty($data['targetMarkerId'])) {
            return $this->createMessageResponse('error', '参数错误!');
        }

        $this->getMarkerService()->merge($data['sourceMarkerId'], $data['targetMarkerId']);

        return $this->createJsonResponse(true);
    }

    public function markerMetasAction(Request $request, $mediaId)
    {
        $markersMeta = $this->getMarkerService()->findMarkersMetaByMediaId($mediaId);
        return $this->createJsonResponse($markersMeta);
    }

    //新增弹题
    public function addQuestionMarkerAction(Request $request, $courseId, $lessonId)
    {
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
        $questionMarkers  = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($data['markerId']);
        return $this->createJsonResponse($questionMarkers);
    }

    //获取当前播放器的驻点
    public function showMarkersAction(Request $request)
    {
        $data = $request->request->all();
        //$data['markerId'] = isset($data['markerId']) ? $data['markerId'] : 0;
        $markers = $this->getMarkerService()->findMarkersByMediaId($data['mediaId']);
        $results = array();
        $user    = $this->getUserService()->getCurrentUser();

        foreach ($markers as $key => $marker) {
            $results[$key]           = $marker;
            $results[$key]['finish'] = $this->getMarkerService()->isFinishMarker($user['id'], $marker['id']);
        }

        return $this->createJsonResponse($results);
    }

    //获取驻点弹题
    public function showMarkerQuestionAction(Request $request)
    {
        $data             = $request->query->all();
        $data['markerId'] = isset($data['markerId']) ? $data['markerId'] : 0;
        $questions        = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId($data['markerId']);
        $user             = $this->getUserService()->getCurrentUser();
        $question         = array();

        foreach ($questions as $key => $value) {
            $questionResult = $this->getQuestionMarkerResultService()->findByUserIdAndQuestionMarkerId($user['id'], $value['id']);

            if (empty($questionResult)) {
                $this->getQuestionMarkerResultService()->addQuestionMarkerResult(array(
                    'markerId'         => $data['markerId'],
                    'questionMarkerId' => $value['id'],
                    'userId'           => $user['id'],
                    'status'           => 'none',
                    'createdTime'      => time(),
                    'updatedTime'      => time()
                ));
                $questionResult = $this->getQuestionMarkerResultService()->findByUserIdAndQuestionMarkerId($user['id'], $value['id']);
            }

            if ($questionResult['status'] == 'none') {
                $question = $value;
                break;
            }
        }

        return $this->render('TopxiaWebBundle:Marker:question-modal.html.twig', array(
            'markerId' => $data['markerId'],
            'question' => $question
        ));
    }

    public function doNextTestAction(Request $request)
    {
        $data               = $request->query->all();
        $data['markerId']   = isset($data['markerId']) ? $data['markerId'] : 0;
        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $user               = $this->getUserService()->getCurrentUser();
        $this->getQuestionMarkerResultService()->finishCurrentQuestion($user['id'], $data['questionId']);
        $conditions = array(
            'markerId' => $data['markerId']
        );
        $questions = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('seq', 'ASC'), 0, 999);

        $question = array();

        foreach ($questions as $key => $value) {
            $questionMarkerResult = $this->getQuestionMarkerResultService()->findByUserIdAndQuestionMarkerId($user['id'], $value['id']);

            if ($questionMarkerResult['status'] == 'none') {
                $question = $value;
                break;
            }
        }

        return $this->createJsonResponse($data['markerId']);
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

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
