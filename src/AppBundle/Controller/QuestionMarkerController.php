<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
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
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;

class QuestionMarkerController extends BaseController
{
    //新的云播放器需要的弹题数据
    public function showQuestionMakersAction(Request $request, $mediaId)
    {
        $questionMakers = $this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId($mediaId);
        $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($questionMakers, 'questionId'), true);
        $baseUrl = $request->getSchemeAndHttpHost();
        $results = [];

        $headerLength = 0;
        if (!$this->getWebExtension()->isHiddenVideoHeader()) {
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            if (!empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus']) {
                $headerLength = $videoHeaderFile['length'];
            }
        }

        foreach ($questionMakers as $index => $questionMaker) {
            if (empty($items[$questionMaker['questionId']]) || empty($items[$questionMaker['questionId']]['questions'])) {
                continue;
            }
            $item = $items[$questionMaker['questionId']];
            $question = array_shift($item['questions']);
            $isChoice = in_array($item['type'], ['choice', 'single_choice', 'uncertain_choice']);
            $isFill = 'fill' == $item['type'];

            $result = [];
            $result['id'] = $questionMaker['id'];
            $result['questionMarkerId'] = $questionMaker['id'];
            $result['markerId'] = $questionMaker['markerId'];
            $result['time'] = $questionMaker['second'] + $headerLength;
            $result['type'] = $item['type'];
            $result['question'] = self::convertAbsoluteUrl($baseUrl, $question['stem']);
            if ($isChoice) {
                if (!empty($question['response_points'])) {
                    foreach ($question['response_points'] as $key => $point) {
                        $options = array_shift($point);
                        $result['options'][$key]['option_key'] = $options['val'];
                        $result['options'][$key]['option_val'] = $options['text'];
                    }
                }
            }
            if ($isFill) {
                $key = 0;
                $result['question'] = preg_replace_callback('/\[\[.*?]]/', function () use ($question, &$key) {
                    return empty($question['answer'][$key]) ? '[[]]' : '[['.$question['answer'][$key++].']]';
                }, $result['question']);
            }
            $answers = $question['answer'];
            foreach ($answers as $answerIndex => $answer) {
                $result['answer'][$answerIndex] = $answer;
            }
            $result['analysis'] = self::convertAbsoluteUrl($baseUrl, $question['analysis']);
            $results[] = $result;
        }

        return $this->createJsonResponse($results);
    }

    /**
     * 视频弹题预览.
     *
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

        $item = $this->getItemService()->getItemWithQuestions($id, true);

        if (empty($item)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
        if (!$this->getQuestionBankService()->canManageBank($questionBank['id'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }

        return $this->render(
            'question-manage/preview-modal.html.twig',
            [
                'item' => $item,
            ]
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
        $data = isset($data['questionIds']) ? $data['questionIds'] : [];
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
        $item = $this->getItemService()->getItem($data['questionId']);

        if (empty($item)) {
            return $this->createMessageResponse('error', '该题目不存在!');
        }
        $bank = $this->getQuestionBankService()->getQuestionBankByItemBankId($item['bank_id']);
        if (empty($bank)) {
            return $this->createMessageResponse('error', '题库不存在');
        }

        if (!$this->getQuestionBankService()->canManageBank($bank['id'])) {
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

        $user = $this->getCurrentUser();
        $data['userId'] = $user['id'];
        $this->getQuestionMarkerResultService()->finishQuestionMarker($data['questionMarkerId'], $data);

        return $this->createJsonResponse(['success' => 1]);
    }

    public function questionAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $video = $this->getActivityService()->getActivity($task['activityId'], true);

        if ($course['id'] != $task['courseId']) {
            $task = $video = [];
        }

        return $this->render(
            'marker/question.html.twig',
            [
                'course' => $course,
                'task' => $task,
                'video' => $video,
                'questionBankChoices' => $this->getQuestionBankChoices(),
            ]
        );
    }

    public function searchAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        list($paginator, $items) = $this->getPaginatorAndQuestion($request, $task);

        return $this->render(
            'marker/question-tr.html.twig',
            [
                'course' => $course,
                'task' => $task,
                'paginator' => $paginator,
                'items' => $items,
            ]
        );
    }

    protected function getQuestionBankChoices()
    {
        $questionBanks = $this->getQuestionBankService()->findUserManageBanks();

        $choices = [];
        foreach ($questionBanks as &$questionBank) {
            $choices[$questionBank['id']] = $questionBank['name'];
        }

        return $choices;
    }

    protected function getPaginatorAndQuestion(Request $request, $task)
    {
        $fields = $request->request->all();

        if (empty($fields['bankId'])) {
            return [new Paginator($request, 0), []];
        }
        if (!$this->getQuestionBankService()->canManageBank($fields['bankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $bank = $this->getQuestionBankService()->getQuestionBank($fields['bankId']);
        $conditions = [
            'bank_id' => $bank['itemBankId'],
            'types' => ['determine', 'single_choice', 'uncertain_choice', 'fill', 'choice'],
            'category_id' => $fields['categoryId'],
        ];
        if (!empty($fields['keyword'])) {
            $conditions['material'] = $fields['keyword'];
        }

        $paginator = new Paginator(
            $request,
            $this->getItemService()->countItems($conditions),
            empty($fields['pageSize']) ? 1 : $fields['pageSize']
        );

        $items = $this->getItemService()->searchItems(
            $conditions,
            ['created_time' => 'DESC'],
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

        foreach ($items as $key => $item) {
            $items[$key]['exist'] = in_array($item['id'], $questionMarkerIds) ? true : false;
        }

        return [$paginator, $items];
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
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }
}
