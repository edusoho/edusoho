<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\CategoryService;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function questionPickerAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();
        $conditions['parentId'] = 0;
        if (!empty($conditions['excludeIds'])) {
            $excludeQuestions = $this->getQuestionService()->findQuestionsByIds(explode(',', $conditions['excludeIds']));
            $questionBankIds = ArrayToolkit::column($excludeQuestions, 'bankId');
            $questionBankIds = array_unique($questionBankIds);
            $questionBankId = array_shift($questionBankIds);
            if (!empty($questionBankIds)) {
                return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.no_multi_question_bank.message'));
            }
            if (!$this->getQuestionBankService()->canManageBank($questionBankId)) {
                $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
            }
        }

        $parameters = array(
            'isSelectBank' => 1,
        );

        if (!empty($questionBankId)) {
            $conditions['bankId'] = $questionBankId;
            $paginator = new Paginator(
                $request,
                $this->getQuestionService()->searchCount($conditions),
                10
            );

            $questions = $this->getQuestionService()->search(
                $conditions,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $questionCategories = $this->getQuestionCategoryService()->findCategories($questionBankId);
            $questionCategories = ArrayToolkit::index($questionCategories, 'id');
            $parameters['questionCategories'] = $questionCategories;
            $parameters['questions'] = $questions;
            $parameters['paginator'] = $paginator;
            $parameters['questionBank'] = $this->getQuestionBankService()->getQuestionBank($questionBankId);
            $parameters['categories'] = $this->getQuestionCategoryService()->getCategoryStructureTree($questionBankId);
            $parameters['excludeIds'] = empty($conditions['excludeIds']) ? '' : $conditions['excludeIds'];
        }

        return $this->render('question-bank/widgets/question-pick-modal.html.twig', $parameters);
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $questionIds = $request->request->get('questionIds', array(0));

        if (!$questionIds) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.must_choose_question.message'));
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $questionBankIds = ArrayToolkit::column($questions, 'bankId');
        $questionBankIds = array_unique($questionBankIds);
        $questionBankId = array_shift($questionBankIds);
        if (!empty($questionBankIds)) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.no_multi_question_bank.message'));
        }
        if (!$this->getQuestionBankService()->canManageBank($questionBankId)) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }

        foreach ($questions as &$question) {
            if ($question['subCount'] > 0) {
                $question['subs'] = $this->getQuestionService()->findQuestionsByParentId($question['id']);
            }
        }

        $categories = $this->getQuestionCategoryService()->findCategories($questionBankId);
        $categories = ArrayToolkit::index($categories, 'id');

        return $this->render('homework/manage/question-picked.html.twig', array(
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($questionBankId),
            'categories' => $categories,
            'questions' => $questions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
        ));
    }

    public function checkAction(Request $request, $resultId, $targetId, $source = 'course')
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$result) {
            return $this->createMessageResponse('error', '该作业结果不存在');
        }

        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($result['testId'], $result['type']);
        if (!$homework) {
            return $this->createMessageResponse('error', '该作业不存在');
        }

        $canCheck = $this->getTestpaperService()->canLookTestpaper($result['id']);
        if (!$canCheck) {
            return $this->createMessageResponse('warning', '没有权限查看');
        }

        if ('doing' == $result['status']) {
            $this->createNewException(TestpaperException::DOING_TESTPAPER());
        }

        if ('finished' == $result['status']) {
            return $this->redirect($this->generateUrl('homework_result_show', array('resultId' => $result['id'])));
        }

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $isContinue = $formData['isContinue'];
            unset($formData['isContinue']);
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            $data = array('success' => true, 'goto' => '');
            if ($isContinue) {
                $route = $this->getRedirectRoute('nextCheck', $source);
                $data['goto'] = $this->generateUrl($route, array('id' => $targetId, 'activityId' => $result['lessonId']));
            }

            return $this->createJsonResponse($data);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $result['id']);

        $essayQuestions = $this->getCheckedEssayQuestions($questions);

        $student = $this->getUserService()->getUser($result['userId']);

        return $this->render('homework/manage/teacher-check.html.twig', array(
            'paper' => $homework,
            'paperResult' => $result,
            'questions' => $essayQuestions,
            'student' => $student,
            'questionTypes' => array('essay', 'material'),
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'total' => array(),
            'action' => $request->query->get('action', ''),
        ));
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
        $activity = $this->getActivityService()->getActivity($activityId);

        if (!$activity || 'homework' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument Invalid');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        $userFirstResults = $this->getTestpaperService()->findResultsByTestIdAndActivityId($testpaper['id'], $activity['id']);

        $data = $this->fillGraphData($userFirstResults);
        $analysis = $this->analysisFirstResults($userFirstResults);

        $task = $this->getCourseTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('homework/manage/result-graph-modal.html.twig', array(
            'activity' => $activity,
            'testpaper' => $testpaper,
            'data' => $data,
            'analysis' => $analysis,
            'task' => $task,
        ));
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        foreach ($questions as $question) {
            if ('essay' == $question['type'] && !$question['parentId']) {
                $essayQuestions[$question['id']] = $question;
            } elseif ('material' == $question['type']) {
                $types = ArrayToolkit::column($question['subs'], 'type');
                if (in_array('essay', $types)) {
                    $essayQuestions[$question['id']] = $question;
                }
            }
        }

        return $essayQuestions;
    }

    protected function getQuestionRanges($courseId)
    {
        if (empty($courseId)) {
            return array();
        }

        $courseTasks = $this->getCourseTaskService()->findTasksByCourseId($courseId);

        return ArrayToolkit::index($courseTasks, 'id');
    }

    protected function sortType($types)
    {
        $newTypes = array('single_choice', 'choice', 'uncertain_choice', 'fill', 'determine', 'essay', 'material');

        foreach ($types as $key => $value) {
            if (!in_array($value, $newTypes)) {
                $k = array_search($value, $newTypes);
                unset($newTypes[$k]);
            }
        }

        return $newTypes;
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

    protected function fillGraphData($userFirstResults)
    {
        $data = array('xScore' => array(), 'yFirstNum' => array(), 'yMaxNum' => array());
        $status = $this->get('codeages_plugin.dict_twig_extension')->getDict('passedStatus');

        $firstStatusGroup = ArrayToolkit::group($userFirstResults, 'firstPassedStatus');
        $maxStatusGroup = ArrayToolkit::group($userFirstResults, 'maxPassedStatus');

        foreach ($status as $key => $name) {
            $data['xScore'][] = $name;
            $data['yFirstNum'][] = empty($firstStatusGroup[$key]) ? 0 : count($firstStatusGroup[$key]);
            $data['yMaxNum'][] = empty($maxStatusGroup[$key]) ? 0 : count($maxStatusGroup[$key]);
        }

        return json_encode($data);
    }

    protected function analysisFirstResults($userFirstResults)
    {
        if (empty($userFirstResults)) {
            return array('passPercent' => 0);
        }

        $data = array();
        $count = 0;
        foreach ($userFirstResults as $result) {
            if ('unpassed' != $result['firstPassedStatus']) {
                ++$count;
            }
        }

        $data['passPercent'] = round($count / count($userFirstResults) * 100, 1);

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

    protected function getQuestionAnalysisService()
    {
        return $this->createService('Question:QuestionAnalysisService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
