<?php

namespace AppBundle\Controller;

use Biz\Accessor\AccessorInterface;
use Biz\Activity\Service\ActivityService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\File\Service\UploadFileService;
use Biz\Marker\Service\MarkerService;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Biz\Question\QuestionException;
use Biz\Question\Service\QuestionService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class QuestionMarkerController extends BaseController
{
    //新的云播放器需要的弹题数据
    public function showQuestionMakersAction(Request $request, $mediaId)
    {
        $questionMakers = $this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId($mediaId);
        $baseUrl = $request->getSchemeAndHttpHost();
        $headerLength = 0;
        if (!$this->getWebExtension()->isHiddenVideoHeader()) {
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            if (!empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus']) {
                $headerLength = $videoHeaderFile['length'];
            }
        }
        $result = array();

        foreach ($questionMakers as $index => $questionMaker) {
            $isChoice = in_array($questionMaker['type'], array('choice', 'single_choice', 'uncertain_choice'));
            $isDetermine = 'determine' == $questionMaker['type'];

            $result[$index]['id'] = $questionMaker['id'];
            $result[$index]['questionMarkerId'] = $questionMaker['id'];
            $result[$index]['markerId'] = $questionMaker['markerId'];
            $result[$index]['time'] = $questionMaker['second'] + $headerLength;
            $result[$index]['type'] = $questionMaker['type'];
            $result[$index]['question'] = self::convertAbsoluteUrl($baseUrl, $questionMaker['stem']);
            if ($isChoice) {
                $questionMetas = $questionMaker['metas'];
                if (!empty($questionMetas['choices'])) {
                    foreach ($questionMetas['choices'] as $choiceIndex => $choice) {
                        $result[$index]['options'][$choiceIndex]['option_key'] = chr(65 + $choiceIndex);
                        $result[$index]['options'][$choiceIndex]['option_val'] = self::convertAbsoluteUrl(
                            $baseUrl,
                            $choice
                        );
                    }
                }
            }
            $answers = $questionMaker['answer'];
            foreach ($answers as $answerIndex => $answer) {
                if ($isChoice) {
                    $result[$index]['answer'][$answerIndex] = chr(65 + $answer);
                } elseif ($isDetermine) {
                    $result[$index]['answer'][$answerIndex] = 1 == $answer ? 'T' : 'F';
                } else {
                    $result[$index]['answer'][$answerIndex] = $answer;
                }
            }
            $result[$index]['analysis'] = self::convertAbsoluteUrl($baseUrl, $questionMaker['analysis']);
        }

        return $this->createJsonResponse($result);
    }

    /**
     * 视频弹题预览.
     *
     * @param Request $request
     * @param $courseId
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function questionMakerPreviewAction(Request $request, $courseId, $id)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $question = $this->getQuestionService()->get($id);

        if (empty($question)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        if (!$this->getQuestionBankService()->canManageBank($question['bankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }

        $item = array(
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'question' => $question,
        );

        if ($question['subCount'] > 0) {
            $questions = $this->getQuestionService()->findQuestionsByParentId($id);

            $items = array();
            foreach ($questions as $value) {
                $items[] = array(
                    'questionId' => $value['id'],
                    'questionType' => $value['type'],
                    'question' => $value,
                );
            }

            $item['items'] = $items;
        }

        $type = in_array($question['type'], array('single_choice', 'uncertain_choice')) ? 'choice' : $question['type'];
        $questionPreview = true;

        return $this->render(
            'marker/question-preview/preview-modal.html.twig',
            array(
                'item' => $item,
                'type' => $type,
                'questionPreview' => $questionPreview,
            )
        );
    }

    //删除弹题
    public function deleteQuestionMarkerAction(Request $request)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();
        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $result = $this->getQuestionMarkerService()->deleteQuestionMarker($data['questionId']);

        return $this->createJsonResponse($result);
    }

    //弹题排序
    public function sortQuestionMarkerAction(Request $request)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();
        $data = isset($data['questionIds']) ? $data['questionIds'] : array();
        $result = $this->getQuestionMarkerService()->sortQuestionMarkers($data);

        return $this->createJsonResponse($result);
    }

    //新增弹题
    public function addQuestionMarkerAction(Request $request, $courseId, $taskId)
    {
        if (!$this->tryManageQuestionMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();

        $task = $this->getTaskService()->getCourseTask($courseId, $taskId);

        if (empty($task)) {
            return $this->createMessageResponse('error', '该课时不存在!');
        }
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $data['questionId'] = isset($data['questionId']) ? $data['questionId'] : 0;
        $question = $this->getQuestionService()->get($data['questionId']);

        if (empty($question)) {
            return $this->createMessageResponse('error', '该题目不存在!');
        }
        if (!$this->getQuestionBankService()->canManageBank($question['bankId'])) {
            return $this->createMessageResponse('error', '没有管理该题目的权限');
        }

        if (empty($data['markerId'])) {
            $result = $this->getMarkerService()->addMarker($activity['ext']['file']['id'], $data);

            return $this->createJsonResponse($result);
        } else {
            $marker = $this->getMarkerService()->getMarker($data['markerId']);

            if (!empty($marker)) {
                $questionmarker = $this->getQuestionMarkerService()->addQuestionMarker(
                    $data['questionId'],
                    $marker['id'],
                    $data['seq']
                );

                return $this->createJsonResponse($questionmarker);
            } else {
                return $this->createJsonResponse(false);
            }
        }
    }

    public function finishQuestionMarkerAction(Request $request)
    {
        $data = $request->request->all();

        $access = $this->getCourseService()->canLearnCourse($data['courseId']);

        if (AccessorInterface::SUCCESS !== $access['code']) {
            $this->createNewException(CourseException::FORBIDDEN_LEARN_COURSE());
        }

        if (in_array($data['type'], array('uncertain_choice', 'single_choice', 'choice'))) {
            foreach ($data['answer'] as &$answerItem) {
                $answerItem = (string) (ord($answerItem) - 65);
            }
        } elseif ('determine' == $data['type']) {
            foreach ($data['answer'] as &$answerItem) {
                $answerItem = 'T' == $answerItem ? '1' : '0';
            }
        }

        $user = $this->getCurrentUser();
        $data['userId'] = $user['id'];
        $this->getQuestionMarkerResultService()->finishQuestionMarker($data['questionMarkerId'], $data);

        return $this->createJsonResponse(array('success' => 1));
    }

    public function questionAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $video = $this->getActivityService()->getActivity($task['activityId'], true);

        if ($course['id'] != $task['courseId']) {
            $task = $video = array();
        }

        return $this->render(
            'marker/question.html.twig',
            array(
                'course' => $course,
                'task' => $task,
                'video' => $video,
                'questionBankChoices' => $this->getQuestionBankChoices(),
            )
        );
    }

    public function searchAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        list($paginator, $questions) = $this->getPaginatorAndQuestion($request, $task);

        return $this->render(
            'marker/question-tr.html.twig',
            array(
                'course' => $course,
                'task' => $task,
                'paginator' => $paginator,
                'questions' => $questions,
            )
        );
    }

    protected function getQuestionBankChoices()
    {
        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            $questionBanks = $this->getQuestionBankService()->findAllQuestionBanks();
        } else {
            $members = $this->getMemberService()->findMembersByUserId($user->getId());
            $questionBankIds = ArrayToolkit::column($members, 'bankId');
            $conditions['ids'] = $questionBankIds ?: array(-1);
            $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $this->getQuestionBankService()->countQuestionBanks($conditions)
            );
        }

        $choices = array();
        foreach ($questionBanks as &$questionBank) {
            $choices[$questionBank['id']] = $questionBank['name'];
        }

        return $choices;
    }

    protected function getPaginatorAndQuestion(Request $request, $task)
    {
        $conditions = $request->request->all();

        if (empty($conditions['bankId'])) {
            return array(new Paginator($request, 0), array());
        }
        if (!$this->getQuestionBankService()->canManageBank($conditions['bankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }
        $conditions['parentId'] = 0;
        $conditions['types'] = array('determine', 'single_choice', 'uncertain_choice', 'fill', 'choice');

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            empty($conditions['pageSize']) ? 1 : $conditions['pageSize']
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $video = $this->getActivityService()->getActivity($task['activityId'], true);

        $file = $video['ext']['file'];

        $markerIds = ArrayToolkit::column($this->getMarkerService()->findMarkersByMediaId($file['id']), 'id');
        $questionMarkerIds = ArrayToolkit::column(
            $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds($markerIds),
            'questionId'
        );

        foreach ($questions as $key => $question) {
            $questions[$key]['exist'] = in_array($question['id'], $questionMarkerIds) ? true : false;
        }

        return array($paginator, $questions);
    }

    protected function tryManageQuestionMarker()
    {
        $user = $this->getCurrentUser();

        if ($this->getUserService()->hasAdminRoles($user['id'])) {
            return true;
        }

        if (in_array('ROLE_TEACHER', $user['roles'])) {
            return true;
        }

        return false;
    }

    protected function convertAbsoluteUrl($baseUrl, $html)
    {
        $html = preg_replace_callback(
            '/src=[\'\"]\/(.*?)[\'\"]/',
            function ($matches) use ($baseUrl) {
                return "src=\"{$baseUrl}/{$matches[1]}\"";
            },
            $html
        );

        return $html;
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->getBiz()->service('Marker:QuestionMarkerService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->getBiz()->service('Marker:MarkerService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return QuestionMarkerResultService
     */
    protected function getQuestionMarkerResultService()
    {
        return $this->getBiz()->service('Marker:QuestionMarkerResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }
}
