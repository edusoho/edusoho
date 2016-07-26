<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class QuestionMarkerController extends BaseController
{
    //新的云播放器需要的弹题数据
    public function showQuestionMakersAction(Request $request, $mediaId)
    {
        $questionMakers = $this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId($mediaId);

        $baseUrl = $request->getSchemeAndHttpHost();

        $result = array();

        foreach ($questionMakers as $index => $questionMaker) {
            $isChoice    = in_array($questionMaker['type'], array('choice', 'single_choice', 'uncertain_choice'));
            $isDetermine = $questionMaker['type'] == 'determine';

            $result[$index]['id']       = $questionMaker['id'];
            $result[$index]['markerId'] = $questionMaker['markerId'];
            $result[$index]['time']     = $questionMaker['second'];
            $result[$index]['type']     = $questionMaker['type'];
            $result[$index]['question'] = self::convertAbsoluteUrl($baseUrl, $questionMaker['stem']);
            if ($isChoice) {
                $questionMetas = json_decode($questionMaker['metas'], true);
                if (!empty($questionMetas['choices'])) {
                    foreach ($questionMetas['choices'] as $choiceIndex => $choice) {
                        $result[$index]['options'][$choiceIndex]['option_key'] = chr(65 + $choiceIndex);
                        $result[$index]['options'][$choiceIndex]['option_val'] = self::convertAbsoluteUrl($baseUrl, $choice);
                    }
                }
            }
            $answers = json_decode($questionMaker['answer'], true);
            foreach ($answers as $answerIndex => $answer) {
                if ($isChoice) {
                    $result[$index]['answer'][$answerIndex] = chr(65 + $answer);
                } elseif ($isDetermine) {
                    $result[$index]['answer'][$answerIndex] = $answer == 1 ? 'T' : 'F';
                } else {
                    $result[$index]['answer'][$answerIndex] = $answer;
                }
            }
            $result[$index]['analysis'] = self::convertAbsoluteUrl($baseUrl, $questionMaker['analysis']);
        }

        return $this->createJsonResponse($result);
    }

    public function sortQuestionAction(Request $Request, $markerId)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();
        $ids  = $data['ids'];
        $this->getQuestionMarkerService()->sortQuestionMarkers($ids);
        return $this->createJsonResponse(true);
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

    public function finishQuestionMarkerAction(Request $request, $markerId, $questionMarkerId)
    {
        $data = $request->request->all();

        $answer = $data['answer'];
        if (in_array($data['type'], array('choice', 'single_choice'))) {
            foreach ($answer as &$answerItem) {
                $answerItem = (string) (ord($answerItem) - 65);
            }
        } elseif ($data['type'] == 'determine') {
            foreach ($answer as &$answerItem) {
                $answerItem == 'T' ? 1 : 0;
            }
        }

        $user                 = $this->getUserService()->getCurrentUser();
        $questionMarkerResult = $this->getQuestionMarkerResultService()->finishCurrentQuestion($markerId, $user['id'], $questionMarkerId, $answer, $data['type'], $data['lessonId']);

        $data = array(
            'markerId'               => $markerId,
            'questionMarkerResultId' => $questionMarkerResult['id']
        );
        return $this->createJsonResponse($data);
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

    protected function convertAbsoluteUrl($baseUrl, $html)
    {
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function ($matches) use ($baseUrl) {
            return "src=\"{$baseUrl}/{$matches[1]}\"";
        }, $html);

        return $html;
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
