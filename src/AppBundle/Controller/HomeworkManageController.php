<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function questionPickerAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        $conditions['courseSetId'] = $courseSet['id'];
        $conditions['parentId'] = 0;

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        return $this->render('homework/manage/question-picker.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'replace' => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator' => $paginator,
            'courseTasks' => $this->getQuestionRanges($request->query->get('courseId', 0)),
            'conditions' => $conditions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courses' => $manageCourses,
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $questionIds = $request->request->get('questionIds', array(0));

        if (!$questionIds) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => 'json_response.must_choose_question.message'));
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questions as &$question) {
            if ($question['subCount'] > 0) {
                $question['subs'] = $this->getQuestionService()->findQuestionsByParentId($question['id']);
            }
        }

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');
        $courseTasks = $this->getCourseTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $this->render('homework/manage/question-picked.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
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

        if ($result['status'] == 'doing') {
            throw $this->createNotFoundException('您所批阅的作业不存在！');
        }

        if ($result['status'] == 'finished') {
            return $this->redirect($this->generateUrl('homework_result_show', array('resultId' => $result['id'])));
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            return $this->createJsonResponse(true);
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

        if (empty($activity) || $activity['mediaType'] != 'homework') {
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

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        foreach ($questions as $question) {
            if ($question['type'] == 'essay' && !$question['parentId']) {
                $essayQuestions[$question['id']] = $question;
            } elseif ($question['type'] == 'material') {
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

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

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
