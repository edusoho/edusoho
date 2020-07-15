<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DateToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Course\Service\ThreadService;
use Biz\File\Service\UploadFileService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SyncEventService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseManageController extends BaseController
{
    public function createAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $data = $this->prepareExpiryMode($data);

            $this->getCourseService()->createCourse($data);

            return $this->redirect(
                $this->generateUrl('course_set_manage_courses', ['courseSetId' => $courseSetId])
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render(
            'course-manage/create-modal.html.twig',
            [
                'courseSet' => $courseSet,
            ]
        );
    }

    public function copyAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $data = $this->prepareExpiryMode($data);

            $this->getCourseService()->copyCourse($data);

            return $this->redirect(
                $this->generateUrl('course_set_manage_courses', ['courseSetId' => $courseSetId])
            );
        }

        $courseId = $request->query->get('courseId');
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if ('end_date' == $course['expiryMode']) {
            $course['deadlineType'] = 'end_date';
            $course['expiryMode'] = 'days';
        }

        return $this->render(
            'course-manage/create-modal.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
            ]
        );
    }

    public function replayAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $courseSetId,
                    'sideNav' => 'replay',
                ]
            );
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);

        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($course['id']);

        $liveTasks = array_filter(
            $tasks,
            function ($task) {
                return 'live' === $task['type'] && 'published' === $task['status'];
            }
        );

        foreach ($liveTasks as $key => $task) {
            $task['isEnd'] = $this->get('web.twig.live_extension')->isLiveFinished($task['activityId'], 'course');
            $task['file'] = $this->_getLiveReplayMedia($task);
            $liveTasks[$key] = $task;
        }

        $default = $this->getSettingService()->get('default', []);
        $lessons = $this->getCourseLessonService()->findLessonsByCourseId($courseId);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render(
            'course-manage/live-replay/index.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
                'tasks' => $liveTasks,
                'default' => $default,
                'lessons' => $lessons,
            ]
        );
    }

    public function updateTaskReplayTitleAction(Request $request, $courseId, $taskId, $replayId)
    {
        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $this->getLiveReplayService()->updateReplay($replayId, ['title' => $title]);

        return $this->createJsonResponse(true);
    }

    public function uploadReplayAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if ('POST' == $request->getMethod()) {
            $fileId = $request->request->get('fileId', 0);
            $this->getActivityService()->updateActivity($activity['id'], ['fileId' => $fileId]);

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_replay',
                    [
                        'courseSetId' => $course['courseSetId'],
                        'courseId' => $course['id'],
                    ]
                )
            );
        }

        if ('videoGenerated' == $activity['ext']['replayStatus']) {
            $task['media'] = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        return $this->render(
            'course-manage/live-replay/upload-modal.html.twig',
            [
                'course' => $course,
                'task' => $task,
                'activity' => $activity,
            ]
        );
    }

    public function editTaskReplayAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $replays = $this->getLiveReplayService()->findReplayByLessonId($activity['id']);

        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('visibleReplays');
            $this->getLiveReplayService()->updateReplayShow($ids, $activity['id']);

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_replay',
                    [
                        'courseSetId' => $course['courseSetId'],
                        'courseId' => $course['id'],
                    ]
                )
            );
        }

        return $this->render(
            'course-manage/live-replay/modal.html.twig',
            [
                'replays' => $replays,
                'taskId' => $task['id'],
                'course' => $course,
                'task' => $task,
            ]
        );
    }

    public function createReplayAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $liveId = $activity['ext']['liveId'];
        $provider = $activity['ext']['liveProvider'];
        $resultList = $this->getLiveReplayService()->generateReplay(
            $liveId,
            $course['id'],
            $activity['id'],
            $provider,
            'live'
        );

        if (array_key_exists('error', $resultList)) {
            return $this->createJsonResponse($resultList, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $task['isEnd'] = intval(time() - $task['endTime']) > 0;
        $task['canRecord'] = $this->get('web.twig.live_extension')->canRecord($liveId, $activity['syncId']);

        if ('live' == $task['type']) {
            if ($activity['syncId'] > 0) {
                $result = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveRoomMaxOnline($liveId);
            } else {
                $client = new EdusohoLiveClient();
                $result = $client->getMaxOnline($liveId);
            }

            $this->getTaskService()->setTaskMaxOnlineNum($task['id'], $result['onLineNum']);
        }

        return $this->createJsonResponse(true);
    }

    public function listAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $courseSetId,
                    'sideNav' => 'tasks',
                ]
            );
        }

        if ('supplier' == $courseSet['platform']) {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $product = $this->getS2B2CProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $courseSet['id'], 'course_set');
            $this->getSyncEventService()->confirmByEvents($product['remoteProductId'], [SyncEventService::EVENT_CLOSE_PLAN]);
        }

        $conditions = [
            'courseSetId' => $courseSet['id'],
        ];

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->countCourses($conditions),
            20
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            ['seq' => 'ASC', 'createdTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($courses, $courseSet) = $this->fillManageRole($courses, $courseSet);

        return $this->render(
            'courseset-manage/courses.html.twig',
            [
                'courseSet' => $courseSet,
                'courses' => $courses,
                'paginator' => $paginator,
            ]
        );
    }

    private function fillManageRole($courses, $courseSet)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin() || ($courseSet['creator'] == $user->getId())) {
            $courseSet['canManage'] = true;
        } else {
            $courseMember = $this->getCourseMemberService()->searchMembers(
                [
                    'courseSetId' => $courseSet['id'],
                    'userId' => $user->getId(),
                    'role' => 'teacher',
                ],
                [],
                0,
                PHP_INT_MAX
            );
            $memberCourseIds = ArrayToolkit::column($courseMember, 'courseId');
            foreach ($courses as &$course) {
                $course['canManage'] = in_array($course['id'], $memberCourseIds);
            }
        }

        return [$courses, $courseSet];
    }

    public function overviewAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $summary = $this->getReportService()->summary($course['id']);

        return $this->render(
            'course-manage/overview/overview.html.twig',
            [
                'summary' => $summary,
                'courseSet' => $courseSet,
                'course' => $course,
            ]
        );
    }

    public function trendencyAction(Request $request, $courseSetId, $courseId)
    {
        $startTime = $request->query->get('startTime');
        $endTime = $request->query->get('endTime');
        $timeRange = [
            'startTime' => $startTime,
            'endTime' => $endTime,
        ];
        $data = $this->getCourseMemberService()->findDailyIncreaseNumByCourseIdAndRoleAndTimeRange($courseId, 'student', $timeRange);
        $data = $this->fillAnalysisData($timeRange, $data);

        return $this->createJsonpResponse($data);
    }

    protected function fillAnalysisData($condition, $currentData)
    {
        $timeRange = $this->getTimeRange($condition);
        $dateRange = DateToolkit::generateDateRange(
            date('Y-m-d', $timeRange['startTime']),
            date('Y-m-d', $timeRange['endTime'])
        );

        $zeroData = [];

        foreach ($dateRange as $key => $value) {
            $zeroData[] = ['date' => $value, 'count' => 0];
        }

        $currentData = ArrayToolkit::index($currentData, 'date');

        $zeroData = ArrayToolkit::index($zeroData, 'date');

        $currentData = array_merge($zeroData, $currentData);

        $currentData = array_values($currentData);

        return $currentData;
    }

    protected function fillAnalysisSum($timeRange, $currentData, $initValue = 0)
    {
        $timeRange = $this->getTimeRange($timeRange);
        $dateRange = DateToolkit::generateDateRange(
            date('Y-m-d', $timeRange['startTime']),
            date('Y-m-d', $timeRange['endTime'])
        );

        $initData = [];

        foreach ($dateRange as $value) {
            $initData[] = ['date' => $value, 'count' => $initValue];
        }

        for ($i = 0; $i < count($initData); ++$i) {
            foreach ($currentData as $value) {
                if (in_array($initData[$i]['date'], $value)) {
                    $initData[$i]['count'] += $value['count'];
                    break;
                }
            }
            if (isset($initData[$i + 1])) {
                $initData[$i + 1]['count'] = $initData[$i]['count'];
            }
        }

        return json_encode($initData);
    }

    protected function getTimeRange($fields)
    {
        $startTime = !empty($fields['startTime']) ? $fields['startTime'] : date('Y-m', time());
        $endTime = !empty($fields['endTime']) ? $fields['endTime'] : date('Y-m-d', time());

        return [
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime) + 24 * 3600 - 1,
        ];
    }

    public function tasksAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_course_students',
                [
                    'courseSetId' => $courseSetId,
                    'courseId' => $courseId,
                ]
            );
        }

        if ('supplier' == $course['platform']) {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $product = $this->getS2B2CProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $course['id'], 'course');
            $this->getSyncEventService()->confirmByEvents($product['remoteProductId'], [SyncEventService::EVENT_CLOSE_TASK]);
        }

        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        $tasksListJsonData = $this->createCourseStrategy($course)->getTasksListJsonData($courseId);

        return $this->render(
            $tasksListJsonData['template'],
            array_merge(
                [
                    'courseSet' => $courseSet,
                    'course' => $course,
                ],
                $tasksListJsonData['data']
            )
        );
    }

    public function prepareExpiryMode($data)
    {
        if (empty($data['expiryMode']) || 'days' != $data['expiryMode']) {
            unset($data['deadlineType']);
        }
        if (!empty($data['deadlineType'])) {
            if ('end_date' == $data['deadlineType']) {
                $data['expiryMode'] = 'end_date';
                if (isset($data['deadline'])) {
                    $data['expiryEndDate'] = $data['deadline'];
                }

                return $data;
            } else {
                $data['expiryMode'] = 'days';

                return $data;
            }
        }

        return $data;
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->canUpdateCourseBaseInfo($courseId, $courseSetId);

        $freeTasks = $this->getTaskService()->findFreeTasksByCourseId($courseId);

        $notifies = [];
        if ('supplier' == $course['platform']) {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $product = $this->getS2B2CProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $course['id'], 'course');
            $notifies = $this->getSyncEventService()->confirmByEvents($product['remoteProductId'], [SyncEventService::EVENT_MODIFY_PRICE]);
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
            if (in_array($courseSet['type'], ['live', 'reservation']) || !empty($courseSet['parentId'])) {
                $this->getCourseSetService()->updateCourseSet($courseSetId, $data);
                unset($data['title']);
                unset($data['subtitle']);
            }
            $data = $this->prepareExpiryMode($data);
            if (!empty($data['services'])) {
                $data['services'] = json_decode($data['services'], true);
            }

            if (!empty($data['freeTaskIds']) || !empty($freeTasks)) {
                $freeTaskIds = ArrayToolkit::column($freeTasks, 'id');
                $canFreeTaskIds = empty($data['freeTaskIds']) ? [] : $data['freeTaskIds'];
                if (!ArrayToolkit::isSameValues($freeTaskIds, $canFreeTaskIds)) {
                    $this->getTaskService()->updateTasks($freeTaskIds, ['isFree' => 0]);
                    $this->getTaskService()->updateTasks($canFreeTaskIds, ['isFree' => 1]);
                }
                unset($data['freeTaskIds']);
            }

            $updatedCourse = $this->getCourseService()->updateBaseInfo($courseId, $data);
            if (empty($course['enableAudio']) && $updatedCourse['enableAudio']) {
                $this->getCourseService()->batchConvert($course['id']);
            }

            return $this->createJsonResponse(true);
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $courseSetId,
                    'sideNav' => 'info',
                ]
            );
        }

        $audioServiceStatus = $this->getUploadFileService()->getAudioServiceStatus();

        $course = $this->formatCourseDate($course);
        if ('end_date' == $course['expiryMode']) {
            $course['deadlineType'] = 'end_date';
            $course['expiryMode'] = 'days';
        }
        $tags = $this->getTagService()->findTagsByOwner([
            'ownerType' => 'course-set',
            'ownerId' => $course['courseSetId'],
        ]);

        return $this->render(
            'course-manage/info.html.twig',
            [
                'courseSet' => $courseSet,
                'tags' => ArrayToolkit::column($tags, 'name'),
                'course' => $course,
                'audioServiceStatus' => $audioServiceStatus,
                'canFreeTasks' => $this->findCanFreeTasks($course),
                'freeTasks' => $freeTasks,
                'notifies' => empty($notifies) ? [] : $notifies,
            ]
        );
    }

    public function headerAction($courseSet, $course)
    {
        return $this->render(
            'course-manage/header.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
            ]
        );
    }

    public function courseRuleAction(Request $request)
    {
        return $this->render('course-manage/rule.html.twig');
    }

    public function liveCapacityAction(Request $request, $courseSetId, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $client = new EdusohoLiveClient();
        $liveCapacity = $client->getCapacity();

        return $this->createJsonResponse($liveCapacity);
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        $freeTasks = $this->getTaskService()->findFreeTasksByCourseId($courseId);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data['enableBuyExpiryTime'])) {
                unset($data['buyExpiryTime']);
            }

            $data = $this->prepareExpiryMode($data);

            if (!empty($data['services'])) {
                $data['services'] = json_decode($data['services'], true);
            }

            $freeTaskIds = ArrayToolkit::column($freeTasks, 'id');
            $this->getTaskService()->updateTasks($freeTaskIds, ['isFree' => 0]);
            if (!empty($data['freeTaskIds'])) {
                $canFreeTaskIds = $data['freeTaskIds'];
                $this->getTaskService()->updateTasks($canFreeTaskIds, ['isFree' => 1]);
                unset($data['freeTaskIds']);
            }

            $this->getCourseService()->updateCourseMarketing($courseId, $data);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_marketing',
                    ['courseSetId' => $courseSetId, 'courseId' => $courseId]
                )
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $courseSetId,
                    'sideNav' => 'marketing',
                ]
            );
        }

        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        //prepare form data
        if ('end_date' == $course['expiryMode']) {
            $course['deadlineType'] = 'end_date';
            $course['expiryMode'] = 'days';
        }

        return $this->render(
            'course-manage/marketing.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $this->formatCourseDate($course),
                'canFreeTasks' => $this->findCanFreeTasks($course),
                'freeTasks' => $freeTasks,
            ]
        );
    }

    private function findCanFreeTasks($course)
    {
        $types = [];
        $activities = $this->getActivityConfig();
        foreach ($activities as $type => $activity) {
            if (isset($activity['canFree']) && $activity['canFree']) {
                $types[] = $type;
            }
        }

        if (empty($types)) {
            return [];
        }

        $conditions = [
            'courseId' => $course['id'],
            'types' => $types,
            'isOptional' => 0,
        ];

        return $this->getTaskService()->searchTasks($conditions, ['seq' => 'ASC'], 0, PHP_INT_MAX);
    }

    protected function sortTasks($tasks)
    {
        $tasks = ArrayToolkit::group($tasks, 'categoryId');
        $modes = [
            'preparation' => 0,
            'lesson' => 1,
            'exercise' => 2,
            'homework' => 3,
            'extraClass' => 4,
        ];

        foreach ($tasks as $key => $taskGroups) {
            uasort(
                $taskGroups,
                function ($item1, $item2) use ($modes) {
                    return $modes[$item1['mode']] > $modes[$item2['mode']];
                }
            );

            $tasks[$key] = $taskGroups;
        }

        return $tasks;
    }

    public function teachersAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data) || !isset($data['teachers'])) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $teachers = json_decode($data['teachers'], true);
            if (empty($teachers)) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $this->getCourseMemberService()->setCourseTeachers($courseId, $teachers);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirectToRoute(
                'course_set_manage_course_teachers',
                ['courseSetId' => $courseSetId, 'courseId' => $courseId]
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $courseSetId,
                    'sideNav' => 'teachers',
                ]
            );
        }

        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $teachers = $this->getCourseService()->findTeachersByCourseId($courseId);
        $teacherIds = [];

        if (!empty($teachers)) {
            foreach ($teachers as $teacher) {
                $teacherIds[] = [
                    'id' => $teacher['userId'],
                    'isVisible' => $teacher['isVisible'],
                    'nickname' => $teacher['nickname'],

                    'avatar' => $this->get('web.twig.extension')->avatarPath($teacher, 'small'),
                ];
            }
        }

        return $this->render(
            'course-manage/teachers.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
                'teacherIds' => $teacherIds,
            ]
        );
    }

    public function teachersMatchAction(Request $request, $courseSetId, $courseId)
    {
        $queryField = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(
            ['nickname' => $queryField, 'roles' => 'ROLE_TEACHER'],
            ['createdTime' => 'DESC'],
            0,
            10
        );

        $teachers = [];

        foreach ($users as $user) {
            $teachers[] = [
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
                'isVisible' => 1,
            ];
        }

        return $this->createJsonResponse($teachers);
    }

    public function closeCheckAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $publishedCourses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);
        if (1 == count($publishedCourses)) {
            return $this->createJsonResponse(
                ['warn' => true, 'message' => "{$course['title']}是课程下唯一发布的教学计划，如果关闭则所在课程也会被关闭。"]
            );
        }

        return $this->createJsonResponse(['warn' => false]);
    }

    public function closeAction(Request $request, $courseSetId, $courseId)
    {
        $this->getCourseService()->closeCourse($courseId);

        return $this->createJsonResponse(['success' => true]);
    }

    public function deleteAction(Request $request, $courseSetId, $courseId)
    {
        $this->getCourseService()->deleteCourse($courseId);
        if (!$this->getCourseSetService()->hasCourseSetManageRole($courseSetId)) {
            return $this->createJsonResponse(['success' => true, 'redirect' => $this->generateUrl('homepage')]);
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function publishAction($courseSetId, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if ('supplier' == $course['platform']) {
            $this->getCourseProductService()->checkCourseStatus($courseId);
        }

        $this->getCourseService()->publishCourse($courseId, true);

        return $this->createJsonResponse(['success' => true]);
    }

    public function prePublishAction($courseSetId, $courseId)
    {
        return $this->createJsonResponse([
            'success' => $this->getCourseService()->hasNoTitleForDefaultPlanInMulPlansCourse($courseId),
        ]);
    }

    public function publishSetTitleAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $defaultPlanTitle = $request->request->get('title');
            $this->getCourseService()->publishAndSetDefaultCourseType($courseId, $defaultPlanTitle);

            return $this->createJsonResponse([
                'success' => true,
            ]);
        }

        return $this->render(
            'course-manage/publish-set-title-modal.html.twig',
            [
                'courseSetId' => $courseSetId,
                'courseId' => $courseId,
            ]
        );
    }

    public function courseItemsSortAction(Request $request, $courseId)
    {
        $ids = $request->request->get('ids', []);
        $this->getCourseService()->sortCourseItems($courseId, $ids);

        return $this->createJsonResponse(['result' => true]);
    }

    public function ordersAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $courseSetting = $this->setting('course');

        if (!$this->getCurrentUser()->isAdmin()
            && (empty($courseSetting['teacher_search_order']) || 1 != $courseSetting['teacher_search_order'])
        ) {
            $this->createNewException(CourseException::SEARCH_ORDER_CLOSED());
        }

        $conditions = $request->query->all();
        $type = 'course';
        $conditions['order_item_target_type'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $conditions['order_item_target_ids'] = [$courseId];

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['start_time'] = strtotime($conditions['startDateTime']);
            $conditions['end_time'] = strtotime($conditions['endDateTime']);
        }

        if (!empty($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['user_id'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['displayStatus'])) {
            $conditions['statuses'] = $this->container->get('web.twig.order_extension')->getOrderStatusFromDisplayStatus($conditions['displayStatus'], 1);
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            10
        );

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $orderIds = ArrayToolkit::column($orders, 'id');
        $orderSns = ArrayToolkit::column($orders, 'sn');

        $orderItems = $this->getOrderService()->findOrderItemsByOrderIds($orderIds);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        $paymentTrades = $this->getPayService()->findTradesByOrderSns($orderSns);
        $paymentTrades = ArrayToolkit::index($paymentTrades, 'order_sn');

        foreach ($orders as &$order) {
            $order['item'] = empty($orderItems[$order['id']]) ? [] : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? [] : $paymentTrades[$order['sn']];
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'user_id'));

        return $this->render(
            'course-manage/order/list.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
                'request' => $request,
                'orders' => $orders,
                'users' => $users,
                'paginator' => $paginator,
            ]
        );
    }

    public function taskLearnDetailAction(Request $request, $courseSetId, $courseId, $taskId)
    {
        $students = [];
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        $count = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($taskId, 'all');
        $paginator = new Paginator($request, $count, 20);

        $results = $this->getTaskResultService()->searchTaskResults(
            ['courseId' => $courseId, 'activityId' => $task['activityId']],
            ['createdTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($results as $key => $result) {
            $user = $this->getUserService()->getUser($result['userId']);
            $students[$key]['nickname'] = $user['nickname'];
            $students[$key]['startTime'] = $result['createdTime'];
            $students[$key]['finishedTime'] = $result['finishedTime'];
            $students[$key]['learnTime'] = round($result['time'] / 60);
            $students[$key]['watchTime'] = round($result['watchTime'] / 60);

            if ('testpaper' == $activity['mediaType']) {
                $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
                $paperResult = $this->getTestpaperService()->getUserFinishedResult(
                    $testpaperActivity['mediaId'],
                    $courseId,
                    $activity['id'],
                    'testpaper',
                    $user['id']
                );
                $students[$key]['result'] = empty($paperResult) ? 0 : $paperResult['score'];
            }
        }

        $task['length'] = intval($activity['length']);

        return $this->render(
            'course-manage/dashboard/task-detail-modal.html.twig',
            [
                'task' => $task,
                'paginator' => $paginator,
                'students' => $students,
            ]
        );
    }

    public function questionMarkerStatsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $taskId = $request->query->get('taskId', 0);

        $stats = $this->getMarkerReportService()->statTaskQuestionMarker($courseId, $taskId);
        $this->sortMarkerStats($stats, $request);

        return $this->render('course-manage/question-marker/stats.html.twig', [
            'courseSet' => $courseSet,
            'course' => $course,
            'stats' => $stats,
        ]);
    }

    public function questionMarkerAnalysisAction(Request $request, $courseSetId, $courseId, $questionMarkerId)
    {
        $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $taskId = $request->query->get('taskId');
        $analysis = $this->getMarkerReportService()->analysisQuestionMarker($courseId, $taskId, $questionMarkerId);

        return $this->render('course-manage/question-marker/analysis.html.twig', [
            'analysis' => $analysis,
        ]);
    }

    public function hidePublishAction(Request $request, $courseId)
    {
        $status = $request->request->get('status', 1);
        $this->getCourseService()->changeHidePublishLesson($courseId, $status);

        return $this->createJsonResponse(true);
    }

    private function sortMarkerStats(&$stats, $request)
    {
        $order = $request->query->get('order', '');
        if ($order) {
            uasort($stats['questionMarkers'], function ($questionMarker1, $questionMarker2) use ($order) {
                if ('desc' == $order) {
                    return $questionMarker1['pct'] < $questionMarker2['pct'];
                } else {
                    return $questionMarker1['pct'] > $questionMarker2['pct'];
                }
            });
        }
    }

    protected function _getLiveReplayMedia(array $task)
    {
        if ('live' == $task['type']) {
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            if ('videoGenerated' == $activity['ext']['replayStatus']) {
                return $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
            } else {
                return [];
            }
        }

        return [];
    }

    protected function formatCourseDate($course)
    {
        if (!empty($course['expiryStartDate'])) {
            $course['expiryStartDate'] = date('Y-m-d', $course['expiryStartDate']);
        }
        if (!empty($course['expiryEndDate'])) {
            $course['expiryEndDate'] = date('Y-m-d', $course['expiryEndDate']);
        }

        return $course;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \AppBundle\Twig\WebExtension
     */
    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return \Biz\Marker\Service\ReportService
     */
    protected function getMarkerReportService()
    {
        return $this->createService('Marker:ReportService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    protected function getActivityConfig()
    {
        return $this->get('extension.manager')->getActivities();
    }

    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return SyncEventService
     */
    protected function getSyncEventService()
    {
        return $this->createService('S2B2C:SyncEventService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
