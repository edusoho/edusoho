<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\CategoryService;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class HomeworkManageController extends BaseController
{
    public function questionPickerAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();
        if (!empty($conditions['exclude_ids'])) {
            $conditions['exclude_ids'] = explode(',', $conditions['exclude_ids']);
            $excludeItems = $this->getItemService()->findItemsByIds($conditions['exclude_ids']);
            $itemBankIds = array_unique(array_column($excludeItems, 'bank_id'));
            if (count($itemBankIds) > 1) {
                return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.no_multi_question_bank.message'));
            }
            $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId(array_shift($itemBankIds));
            if (!$this->getQuestionBankService()->canManageBank($questionBank['id'])) {
                $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
            }
        }

        $parameters = ['isSelectBank' => 1];

        if (!empty($questionBank)) {
            $conditions['bank_id'] = $questionBank['itemBankId'];
            $paginator = new Paginator(
                $request,
                $this->getItemService()->countItems($conditions),
                10
            );

            $items = $this->getItemService()->searchItems(
                $conditions,
                ['created_time' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
            $parameters['itemCategories'] = ArrayToolkit::index($questionCategories, 'id');
            $parameters['items'] = $items;
            $parameters['paginator'] = $paginator;
            $parameters['questionBank'] = $questionBank;
            $parameters['categoryTree'] = $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']);
            $parameters['excludeIds'] = empty($conditions['exclude_ids']) ? '' : implode(',', $conditions['exclude_ids']);
        }

        return $this->render('question-bank/widgets/question-pick-modal.html.twig', $parameters);
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $itemIds = $request->request->get('itemIds', []);

        if (!$itemIds) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.must_choose_question.message'));
        }

        $questions = $this->getItemService()->findItemsByIds($itemIds);

        $itemBankIds = array_unique(array_column($questions, 'bank_id'));
        if (count($itemBankIds) > 1) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.no_multi_question_bank.message'));
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId(array_shift($itemBankIds));
        if (!$this->getQuestionBankService()->canManageBank($questionBank['id'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }

        $categories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);

        return $this->render('homework/manage/question-picked.html.twig', array(
            'questionBank' => $questionBank,
            'categories' => ArrayToolkit::index($categories, 'id'),
            'questions' => $questions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
        ));
    }

    public function checkAction(Request $request, $answerRecordId, $targetId, $source = 'course')
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (!$answerRecord) {
            $this->createNewException(TestpaperException::NOTFOUND_RESULT());
        }

        if ('reviewing' !== $answerRecord['status']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        switch ($source) {
            case 'course':
                $successContinueGotoUrl = $this->generateUrl('course_manage_exam_next_result_check', array('id' => $targetId, 'activityId' => $this->getActivityIdByAnswerSceneId($answerRecord['answer_scene_id'])));
                $this->getCourseService()->tryManageCourse($targetId);
                break;
            case 'classroom':
                $successContinueGotoUrl = $this->generateUrl('classroom_manage_exam_next_result_check', array('id' => $targetId, 'activityId' => $this->getActivityIdByAnswerSceneId($answerRecord['answer_scene_id'])));
                $this->getClassroomService()->tryHandleClassroom($targetId);
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
                break;
        }

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:reviewAnswer', array(
            'answerRecordId' => $answerRecordId,
            'successGotoUrl' => $this->generateUrl('homework_result_show', array('action' => 'check', 'answerRecordId' => $answerRecordId)),
            'successContinueGotoUrl' => $successContinueGotoUrl,
        ));
    }

    protected function getActivityIdByAnswerSceneId($answerSceneId)
    {
        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($answerSceneId);
        return $this->getActivityService()->getByMediaIdAndMediaType($homeworkActivity['id'], 'homework')['id'];
    }

    public function resultAnalysisAction(Request $request, $targetId, $targetType, $activityId, $studentNum)
    {
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || 'homework' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        $analyses = $this->getQuestionAnalysisService()->searchAnalysis(array('activityId' => $activity['id']), array(), 0, PHP_INT_MAX);

        $paper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        $questions = $this->getTestpaperService()->showTestpaperItems($paper['id']);

        $relatedData = $this->findRelatedData($activity, $paper);
        $relatedData['studentNum'] = $studentNum;

        return $this->render('homework/manage/result-analysis.html.twig', array(
            'analyses' => ArrayToolkit::groupIndex($analyses, 'questionId', 'choiceIndex'),
            'paper' => $paper,
            'questions' => $questions,
            'relatedData' => $relatedData,
            'targetType' => $targetType,
        ));
    }

    public function resultGraphAction($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);

        if (!$activity || 'homework' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument Invalid');
        }

        $answerRecords = $this->getAnswerRecordsByAnswerSceneId($activity['ext']['answerSceneId']);
        $firstAndMaxGrade = $this->getCalculateRecordsFirstAndMaxGrade($answerRecords);
        $data = $this->fillGraphData($firstAndMaxGrade);
        $analysis = $this->analysisFirstResults($firstAndMaxGrade);
        $task = $this->getCourseTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('homework/manage/result-graph-modal.html.twig', array(
            'data' => $data,
            'analysis' => $analysis,
            'task' => $task,
        ));
    }

    protected function getAnswerRecordsByAnswerSceneId($answerSceneId)
    {
        $conditions = array(
            'status' => 'finished',
            'answer_scene_id' => $answerSceneId,
        );
        $answerRecords = $this->getAnswerRecordService()->search($conditions, array(), 0, $this->getAnswerRecordService()->count($conditions));
        $answerReports = ArrayToolkit::index(
            $this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')),
            'id'
        );
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['grade'] = $answerReports[$answerRecord['answer_report_id']]['grade'];
        }

        $answerRecords = ArrayToolkit::group($answerRecords, 'user_id');

        return $answerRecords;
    }

    protected function getCalculateRecordsFirstAndMaxGrade($answerRecords)
    {
        $data = array();
        foreach ($answerRecords as $userAnswerRecords) {
            $data[] = array(
                'firstGrade' => $userAnswerRecords[0]['grade'],
                'maxGrade' => $this->getUserMaxGrade($userAnswerRecords),
            );
        }

        return $data;
    }

    protected function getUserMaxGrade($userAnswerRecords)
    {
        if (1 == count($userAnswerRecords)) {
            return $userAnswerRecords[0]['grade'];
        }

        $grades = ArrayToolkit::column($userAnswerRecords, 'grade');
        sort($grades);

        return $grades[0];
    }

    protected function findRelatedData($activity, $paper)
    {
        $relatedData = array();
        $userFirstResults = $this->getTestpaperService()->findExamFirstResults($paper['id'], $paper['type'], $activity['id']);

        $relatedData['total'] = count($userFirstResults);

        $userFirstResults = ArrayToolkit::group($userFirstResults, 'status');
        $finishedResults = empty($userFirstResults['finished']) ? array() : $userFirstResults['finished'];

        $relatedData['finished'] = count($finishedResults);

        return $relatedData;
    }

    protected function fillGraphData($firstAndMaxGrade)
    {
        $data = array('xScore' => array(), 'yFirstNum' => array(), 'yMaxNum' => array());
        $status = $this->get('codeages_plugin.dict_twig_extension')->getDict('passedStatus');

        $firstGradeGroup = ArrayToolkit::group($firstAndMaxGrade, 'firstGrade');
        $maxGradeGroup = ArrayToolkit::group($firstAndMaxGrade, 'maxGrade');

        foreach ($status as $key => $name) {
            $data['xScore'][] = $name;
            $data['yFirstNum'][] = empty($firstGradeGroup[$key]) ? 0 : count($firstGradeGroup[$key]);
            $data['yMaxNum'][] = empty($maxGradeGroup[$key]) ? 0 : count($maxGradeGroup[$key]);
        }

        return json_encode($data);
    }

    protected function analysisFirstResults($firstAndMaxGrade)
    {
        if (empty($firstAndMaxGrade)) {
            return array('passPercent' => 0);
        }

        $data = array();
        $count = 0;
        foreach ($firstAndMaxGrade as $grade) {
            if ('unpassed' != $grade['firstGrade']) {
                ++$count;
            }
        }

        $data['passPercent'] = round($count / count($firstAndMaxGrade) * 100, 1);

        return $data;
    }

    protected function getRedirectRoute($mode, $type)
    {
        $routes = array(
            'nextCheck' => array(
                'course' => 'course_manage_exam_next_result_check',
                'classroom' => 'classroom_manage_exam_next_result_check',
            ),
        );

        return $routes[$mode][$type];
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    protected function getQuestionAnalysisService()
    {
        return $this->createService('Question:QuestionAnalysisService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->createService('Activity:HomeworkActivityService');
    }
}
