<?php

namespace AppBundle\Controller\Testpaper;

use AppBundle\Common\Paginator;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Question\Service\QuestionService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', array(
                'id' => $id,
                'sideNav' => 'testpaper',
            ));
        }

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'type' => 'testpaper',
        );

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updatedUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('testpaper/manage/index.html.twig', array(
            'courseSet' => $courseSet,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
        ));
    }

    public function createAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($request->getMethod() === 'POST') {
            $fields = $request->request->all();

            $fields['courseSetId'] = $courseSet['id'];
            $fields['courseId'] = 0;
            $fields['pattern'] = 'questionType';

            $testpaper = $this->getTestpaperService()->buildTestpaper($fields, 'testpaper');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_testpaper_questions',
                    array('courseSetId' => $courseSet['id'], 'testpaperId' => $testpaper['id'])
                )
            );
        }

        $types = $this->getQuestionTypes();

        $conditions = array(
            'types' => array_keys($types),
            'courseSetId' => $courseSet['id'],
            'parentId' => 0,
        );

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        $user = $this->getUser();
        $ranges = $this->getTaskService()->findUserTeachCoursesTasksByCourseSetId($user['id'], $courseSet['id']);

        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        return $this->render('testpaper/manage/create.html.twig', array(
            'courseSet' => $courseSet,
            'ranges' => $ranges,
            'types' => $types,
            'questionNums' => $questionNums,
            'courses' => $manageCourses,
        ));
    }

    public function checkListAction(Request $request, $targetId, $targetType, $type)
    {
        $courseIds = array($targetId);
        if ($targetType === 'classroom') {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
            $courseIds = ArrayToolkit::column($courses, 'id');
        }

        $conditions = array(
            'courseIds' => empty($courseIds) ? array(-1) : $courseIds,
            'type' => $type,
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($conditions),
            10
        );
        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('seq' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($tasks, $testpapers) = $this->findTestpapers($tasks, $type);
        $resultStatusNum = $this->findTestpapersStatusNum($tasks, $testpapers);

        return $this->render('testpaper/manage/check-list.html.twig', array(
            'testpapers' => $testpapers,
            'paginator' => $paginator,
            'targetId' => $targetId,
            'targetType' => $targetType,
            'tasks' => $tasks,
            'resultStatusNum' => $resultStatusNum,
        ));
    }

    public function checkAction(Request $request, $resultId, $targetId, $source = 'course')
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$result) {
            throw $this->createResourceNotFoundException('testpaperResult', $resultId);
        }
        //还需要是否是教师的权限判断
        if (!$this->getTestpaperService()->canLookTestpaper($result['id'])) {
            return $this->createMessageResponse('error', 'access denied');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($result['testId'], $result['type']);
        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $result['id']);
        }

        if ($result['status'] !== 'reviewing') {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $result['id'])));
        }

        if ($request->getMethod() === 'POST') {
            $formData = $request->request->all();
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            return $this->createJsonResponse(true);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $result['id']);

        $essayQuestions = $this->getCheckedEssayQuestions($questions);

        $student = $this->getUserService()->getUser($result['userId']);
        $accuracy = $this->getTestpaperService()->makeAccuracy($result['id']);
        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        return $this->render('testpaper/manage/teacher-check.html.twig', array(
            'paper' => $testpaper,
            'paperResult' => $result,
            'questions' => $essayQuestions,
            'student' => $student,
            'accuracy' => $accuracy,
            'questionTypes' => $this->getCheckedQuestionType($testpaper),
            'total' => $total,
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'action' => $request->query->get('action', ''),
        ));
    }

    public function resultListAction(Request $request, $testpaperId, $source, $targetId, $activityId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $status = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('testId' => $testpaper['id']);
        if ($status !== 'all') {
            $conditions['status'] = $status;
        }
        $conditions['lessonId'] = $activityId;

        if (!empty($keyword)) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $testpaper['resultStatusNum'] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id'], $activityId);

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('endTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentIds = ArrayToolkit::column($testpaperResults, 'userId');
        $teacherIds = ArrayToolkit::column($testpaperResults, 'checkTeacherId');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/result-list.html.twig', array(
            'testpaper' => $testpaper,
            'status' => $status,
            'paperResults' => $testpaperResults,
            'paginator' => $paginator,
            'users' => $users,
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'keyword' => $keyword,
        ));
    }

    public function buildCheckAction(Request $request, $courseSetId, $type)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $data = $request->request->all();
        $data['courseSetId'] = $courseSet['id'];

        $result = $this->getTestpaperService()->canBuildTestpaper($type, $data);

        return $this->createJsonResponse($result);
    }

    public function updateAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->request->all();
            $this->getTestpaperService()->updateTestpaper($testpaper['id'], $data);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('course_set_manage_testpaper', array('id' => $courseSet['id'])));
        }

        return $this->render('testpaper/manage/update.html.twig', array(
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
        ));
    }

    public function deleteAction(Request $request, $courseSetId, $testpaperId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $this->getTestpaperService()->deleteTestpaper($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $ids = $request->request->get('ids');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);
        if (!empty($testpapers)) {
            foreach ($testpapers as $testpaper) {
                if ($testpaper['courseSetId'] != $courseSetId) {
                    return $this->createMessageResponse('error', 'testpaper not found');
                }
            }
            $this->getTestpaperService()->deleteTestpapers($ids);

            return $this->createJsonResponse(true);
        }

        return $this->createMessageResponse('error', 'testpaper not found');
    }

    public function publishAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            throw new NotFoundException("testpaper#{$testpaperId} not found");
        }

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'courseSet' => $courseSet,
        ));
    }

    public function closeAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            throw new NotFoundException("testpaper#{$testpaperId} not found");
        }

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'courseSet' => $courseSet,
        ));
    }

    public function questionsAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ($testpaper['status'] != 'draft') {
            return $this->createMessageResponse('error', '已发布或已关闭的试卷不能再修改题目');
        }

        if ($request->getMethod() === 'POST') {
            $fields = $request->request->all();

            if (empty($fields['questions'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }

            if (!empty($fields['passedScore'])) {
                $fields['passedCondition'] = array($fields['passedScore']);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $fields);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('course_set_manage_testpaper', array('id' => $courseSetId)),
            ));
        }

        $items = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $hasEssay = $this->getQuestionService()->hasEssay(ArrayToolkit::column($items, 'questionId'));

        $passedScoreDefault = empty($testpaper['passedCondition']) ? ceil($testpaper['score'] * 0.6) : $testpaper['passedCondition'][0];

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        $courseTasks = $this->getQuestionRanges($testpaper['id']);

        return $this->render('testpaper/manage/question.html.twig', array(
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
            'questions' => $questions,
            'hasEssay' => $hasEssay,
            'passedScoreDefault' => $passedScoreDefault,
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
        ));
    }

    public function infoAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $testpaperId = $request->request->get('testpaperId');

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $items = $this->getTestpaperService()->getItemsCountByParams(
            array('testId' => $testpaperId, 'parentIdDefault' => 0),
            $gourpBy = 'questionType'
        );
        $subItems = $this->getTestpaperService()->getItemsCountByParams(
            array('testId' => $testpaperId, 'parentId' => 0)
        );

        $items = ArrayToolkit::index($items, 'questionType');

        $items['material'] = $subItems[0];

        return $this->render('testpaper/manage/item-get-table.html.twig', array(
            'items' => $items,
        ));
    }

    public function previewAction(Request $request, $courseSetId, $testpaperId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ($testpaper['status'] === 'closed') {
            return $this->createMessageResponse('warning', 'testpaper already closed');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/manage/preview.html.twig', array(
            'questions' => $questions,
            'limitedTime' => $testpaper['limitedTime'],
            'paper' => $testpaper,
            'paperResult' => array(),
            'total' => $total,
            'attachments' => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper),
        ));
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        $essayQuestions['essay'] = !empty($questions['essay']) ? $questions['essay'] : array();

        if (empty($questions['material'])) {
            return $essayQuestions;
        }

        foreach ($questions['material'] as $questionId => $question) {
            $questionTypes = ArrayToolkit::column(empty($question['subs']) ? array() : $question['subs'], 'type');

            if (in_array('essay', $questionTypes)) {
                $essayQuestions['material'][$questionId] = $question;
            }
        }

        return $essayQuestions;
    }

    protected function getCheckedQuestionType($testpaper)
    {
        $questionTypes = array();
        if (!empty($testpaper['metas']['counts'])) {
            foreach ($testpaper['metas']['counts'] as $type => $count) {
                if ($count > 0) {
                    $questionTypes[] = $type;
                }
            }
        }

        return $questionTypes;
    }

    protected function getQuestionTypes()
    {
        $typesConfig = $this->get('extension.manager')->getQuestionTypes();

        $types = array();
        foreach ($typesConfig as $type => $typeConfig) {
            $types[$type] = array(
                'name' => $typeConfig['name'],
                'hasMissScore' => $typeConfig['hasMissScore'],
            );
        }

        return $types;
    }

    protected function getQuestionRanges($testpaperId)
    {
        $items = $this->getTestpaperService()->findItemsByTestId($testpaperId);
        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');

        $courseTasks = $this->getTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $courseTasks;
    }

    protected function findTestpapers($tasks, $type)
    {
        if (empty($tasks)) {
            return array($tasks, array());
        }

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        if ($type == 'testpaper') {
            $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');
            $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds($testpaperActivityIds);
            $testpaperActivities = ArrayToolkit::index($testpaperActivities, 'id');
            $ids = ArrayToolkit::column($testpaperActivities, 'mediaId');

            array_walk($tasks, function (&$task, $key) use ($activities, $testpaperActivities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $testpaperActivities[$activity['mediaId']]['mediaId'];
            });
        } else {
            $ids = ArrayToolkit::column($activities, 'mediaId');
            array_walk($tasks, function (&$task, $key) use ($activities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $activity['mediaId'];
            });
        }

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);

        if (empty($testpapers)) {
            return array($activities, array());
        }

        return array($tasks, $testpapers);
    }

    protected function findTestpapersStatusNum($tasks, $testpapers)
    {
        $resultStatusNum = array();
        foreach ($tasks as $task) {
            if (empty($testpapers[$task['testId']])) {
                continue;
            }

            $resultStatusNum[$task['activityId']] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($task['testId'], $task['activityId']);
        }

        return $resultStatusNum;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
