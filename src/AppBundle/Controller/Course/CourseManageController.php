<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\DateToolkit;
use AppBundle\Common\Paginator;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Util\EdusohoLiveClient;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskResultService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\LiveReplayService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\Activity\Service\ActivityLearnLogService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class CourseManageController extends BaseController
{
    public function createAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $data = $this->prepareExpiryMode($data);

            $this->getCourseService()->createCourse($data);

            return $this->redirect(
                $this->generateUrl('course_set_manage_courses', array('courseSetId' => $courseSetId))
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render(
            'course-manage/create-modal.html.twig',
            array(
                'courseSet' => $courseSet,
            )
        );
    }

    public function copyAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $data = $this->prepareExpiryMode($data);

            $this->getCourseService()->copyCourse($data);

            return $this->redirect(
                $this->generateUrl('course_set_manage_courses', array('courseSetId' => $courseSetId))
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
            array(
                'courseSet' => $courseSet,
                'course' => $course,
            )
        );
    }

    public function replayAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'replay',
                )
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
            $task['isEnd'] = (int) (time() - $task['endTime']) > 0;
            $task['file'] = $this->_getLiveReplayMedia($task);
            $liveTasks[$key] = $task;
        }

        $default = $this->getSettingService()->get('default', array());
        $lessons = $this->getCourseLessonService()->findLessonsByCourseId($courseId);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render(
            'course-manage/live-replay/index.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'tasks' => $liveTasks,
                'default' => $default,
                'lessons' => $lessons,
            )
        );
    }

    public function updateTaskReplayTitleAction(Request $request, $courseId, $taskId, $replayId)
    {
        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $this->getLiveReplayService()->updateReplay($replayId, array('title' => $title));

        return $this->createJsonResponse(true);
    }

    public function uploadReplayAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if ('POST' == $request->getMethod()) {
            $fileId = $request->request->get('fileId', 0);
            $this->getActivityService()->updateActivity($activity['id'], array('fileId' => $fileId));

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_replay',
                    array(
                        'courseSetId' => $course['courseSetId'],
                        'courseId' => $course['id'],
                    )
                )
            );
        }

        if ($activity['ext']['replayStatus'] == 'videoGenerated') {
            $task['media'] = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
        }

        return $this->render(
            'course-manage/live-replay/upload-modal.html.twig',
            array(
                'course' => $course,
                'task' => $task,
                'activity' => $activity,
            )
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
                    array(
                        'courseSetId' => $course['courseSetId'],
                        'courseId' => $course['id'],
                    )
                )
            );
        }

        return $this->render(
            'course-manage/live-replay/modal.html.twig',
            array(
                'replays' => $replays,
                'taskId' => $task['id'],
                'course' => $course,
                'task' => $task,
            )
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
        $task['canRecord'] = $this->get('web.twig.live_extension')->canRecord($liveId);

        $client = new EdusohoLiveClient();

        if ('live' == $task['type']) {
            $result = $client->getMaxOnline($liveId);
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
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'tasks',
                )
            );
        }

        $conditions = array(
            'courseSetId' => $courseSet['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->countCourses($conditions),
            20
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array('seq' => 'DESC', 'createdTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($courses, $courseSet) = $this->fillManageRole($courses, $courseSet);

        return $this->render(
            'courseset-manage/courses.html.twig',
            array(
                'courseSet' => $courseSet,
                'courses' => $courses,
                'paginator' => $paginator,
            )
        );
    }

    private function fillManageRole($courses, $courseSet)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin() || ($courseSet['creator'] == $user->getId())) {
            $courseSet['canManage'] = true;
        } else {
            $courseMember = $this->getCourseMemberService()->searchMembers(
                array(
                    'courseSetId' => $courseSet['id'],
                    'userId' => $user->getId(),
                    'role' => 'teacher',
                ),
                array(),
                0,
                PHP_INT_MAX
            );
            $memberCourseIds = ArrayToolkit::column($courseMember, 'courseId');
            foreach ($courses as &$course) {
                $course['canManage'] = in_array($course['id'], $memberCourseIds);
            }
        }

        return array($courses, $courseSet);
    }

    public function overviewAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $summary = $this->getReportService()->summary($course['id']);

        return $this->render(
            'course-manage/overview/overview.html.twig',
            array(
                'summary' => $summary,
                'courseSet' => $courseSet,
                'course' => $course,
            )
        );
    }

    public function trendencyAction(Request $request, $courseSetId, $courseId)
    {
        $startTime = $request->query->get('startTime');
        $endTime = $request->query->get('endTime');
        $timeRange = array(
            'startTime' => $startTime,
            'endTime' => $endTime,
        );
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

        $zeroData = array();

        foreach ($dateRange as $key => $value) {
            $zeroData[] = array('date' => $value, 'count' => 0);
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

        $initData = array();

        foreach ($dateRange as $value) {
            $initData[] = array('date' => $value, 'count' => $initValue);
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

        return array(
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime) + 24 * 3600 - 1,
        );
    }

    public function tasksAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_course_students',
                array(
                    'courseSetId' => $courseSetId,
                    'courseId' => $courseId,
                )
            );
        }

        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        $tasksListJsonData = $this->createCourseStrategy($course)->getTasksListJsonData($courseId);

        return $this->render(
            $tasksListJsonData['template'],
            array_merge(
                array(
                    'courseSet' => $courseSet,
                    'course' => $course,
                ),
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
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $updatedCourse = $this->getCourseService()->updateCourse($courseId, $data);
            if (empty($course['enableAudio']) && $updatedCourse['enableAudio']) {
                $this->getCourseService()->batchConvert($course['id']);
            }

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_info',
                    array('courseSetId' => $courseSetId, 'courseId' => $courseId)
                )
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'info',
                )
            );
        }

        $audioServiceStatus = $this->getUploadFileService()->getAudioServiceStatus();

        return $this->render(
            'course-manage/info.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $this->formatCourseDate($course),
                'audioServiceStatus' => $audioServiceStatus,
            )
        );
    }

    public function headerAction($courseSet, $course)
    {
        return $this->render(
            'course-manage/header.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
            )
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
            $this->getTaskService()->updateTasks($freeTaskIds, array('isFree' => 0));
            if (!empty($data['freeTaskIds'])) {
                $canFreeTaskIds = $data['freeTaskIds'];
                $this->getTaskService()->updateTasks($canFreeTaskIds, array('isFree' => 1));
                unset($data['freeTaskIds']);
            }

            $this->getCourseService()->updateCourseMarketing($courseId, $data);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_course_marketing',
                    array('courseSetId' => $courseSetId, 'courseId' => $courseId)
                )
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'marketing',
                )
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
            array(
                'courseSet' => $courseSet,
                'course' => $this->formatCourseDate($course),
                'canFreeTasks' => $this->findCanFreeTasks($course),
                'freeTasks' => $freeTasks,
            )
        );
    }

    private function findCanFreeTasks($course)
    {
        $types = array();
        $activities = $this->getActivityConfig();
        foreach ($activities as $type => $activity) {
            if (isset($activity['canFree']) && $activity['canFree']) {
                $types[] = $type;
            }
        }

        if (empty($types)) {
            return array();
        }

        $conditions = array(
            'courseId' => $course['id'],
            'types' => $types,
            'isOptional' => 0,
        );

        return $this->getTaskService()->searchTasks($conditions, array('seq' => 'ASC'), 0, PHP_INT_MAX);
    }

    protected function sortTasks($tasks)
    {
        $tasks = ArrayToolkit::group($tasks, 'categoryId');
        $modes = array(
            'preparation' => 0,
            'lesson' => 1,
            'exercise' => 2,
            'homework' => 3,
            'extraClass' => 4,
        );

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
                throw new InvalidArgumentException('Empty Data');
            }

            $teachers = json_decode($data['teachers'], true);
            if (empty($teachers)) {
                throw new InvalidArgumentException('Empty Data');
            }

            $this->getCourseMemberService()->setCourseTeachers($courseId, $teachers);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirectToRoute(
                'course_set_manage_course_teachers',
                array('courseSetId' => $courseSetId, 'courseId' => $courseId)
            );
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $courseSetId,
                    'sideNav' => 'teachers',
                )
            );
        }

        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $teachers = $this->getCourseService()->findTeachersByCourseId($courseId);
        $teacherIds = array();

        if (!empty($teachers)) {
            foreach ($teachers as $teacher) {
                $teacherIds[] = array(
                    'id' => $teacher['userId'],
                    'isVisible' => $teacher['isVisible'],
                    'nickname' => $teacher['nickname'],

                    'avatar' => $this->get('web.twig.extension')->avatarPath($teacher, 'small'),
                );
            }
        }

        return $this->render(
            'course-manage/teachers.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'teacherIds' => $teacherIds,
            )
        );
    }

    public function teachersMatchAction(Request $request, $courseSetId, $courseId)
    {
        $queryField = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(
            array('nickname' => $queryField, 'roles' => 'ROLE_TEACHER'),
            array('createdTime' => 'DESC'),
            0,
            10
        );

        $teachers = array();

        foreach ($users as $user) {
            $teachers[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'avatar' => $this->getWebExtension()->avatarPath($user, 'small'),
                'isVisible' => 1,
            );
        }

        return $this->createJsonResponse($teachers);
    }

    public function closeCheckAction(Request $request, $courseSetId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $publishedCourses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);
        if (1 == count($publishedCourses)) {
            return $this->createJsonResponse(
                array('warn' => true, 'message' => "{$course['title']}是课程下唯一发布的教学计划，如果关闭则所在课程也会被关闭。")
            );
        }

        return $this->createJsonResponse(array('warn' => false));
    }

    public function closeAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->closeCourse($courseId);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function deleteAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->deleteCourse($courseId);
            if (!$this->getCourseSetService()->hasCourseSetManageRole($courseSetId)) {
                return $this->createJsonResponse(array('success' => true, 'redirect' => $this->generateUrl('homepage')));
            }
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }

        return $this->createJsonResponse(array('success' => true));
    }

    public function publishAction($courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->publishCourse($courseId, true);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function courseItemsSortAction(Request $request, $courseId)
    {
        $ids = $request->request->get('ids', array());
        $this->getCourseService()->sortCourseItems($courseId, $ids);

        return $this->createJsonResponse(array('result' => true));
    }

    public function ordersAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $courseSetting = $this->setting('course');

        if (!$this->getCurrentUser()->isAdmin()
            && (empty($courseSetting['teacher_search_order']) || 1 != $courseSetting['teacher_search_order'])
        ) {
            throw $this->createAccessDeniedException('查询订单已关闭，请联系管理员');
        }

        $conditions = $request->query->all();
        $type = 'course';
        $conditions['order_item_target_type'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $conditions['order_item_target_ids'] = array($courseId);

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
            array('created_time' => 'DESC'),
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
            $order['item'] = empty($orderItems[$order['id']]) ? array() : $orderItems[$order['id']];
            $order['trade'] = empty($paymentTrades[$order['sn']]) ? array() : $paymentTrades[$order['sn']];
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'user_id'));

        return $this->render(
            'course-manage/order/list.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'request' => $request,
                'orders' => $orders,
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    public function ordersExportCsvAction(Request $request, $courseSetId, $courseId)
    {
        // $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $courseSetting = $this->setting('course');

        if (!$this->getCurrentUser()->isAdmin()
            && (empty($courseSetting['teacher_search_order']) || 1 != $courseSetting['teacher_search_order'])
        ) {
            throw $this->createAccessDeniedException('查询订单已关闭，请联系管理员');
        }

        $status = array(
            'created' => '未付款',
            'paid' => '已付款',
            'refunding' => '退款中',
            'refunded' => '已退款',
            'cancelled' => '已关闭',
        );
        $payment = array(
            'alipay' => '支付宝',
            'llpay' => '连连支付',
            'heepay' => '汇付宝',
            'quickpay' => '快捷支付',
            'wxpay' => '微信支付',
            'coin' => '虚拟币支付',
            'outside' => '站外支付',
            'none' => '--',
        );

        $conditions = $request->query->all();

        $type = 'course';
        $conditions['targetType'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $conditions['targetId'] = $courseId;

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime'] = strtotime($conditions['endDateTime']);
        }

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            'latest',
            0,
            PHP_INT_MAX
        );

        //        $userinfoFields = array('sn', 'createdTime', 'status', 'targetType', 'amount', 'payment', 'paidTime');

        $studentUserIds = ArrayToolkit::column($orders, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $str = '订单号,订单状态,订单名称,课程名称,订单价格,优惠码,优惠金额,虚拟币支付,实付价格,支付方式,购买者,姓名,操作,创建时间,付款时间';

        $str .= "\r\n";

        $results = array();

        foreach ($orders as $key => $order) {
            $column = '';
            $column .= $order['sn'].',';
            $column .= $status[$order['status']].',';
            $column .= $order['title'].',';
            $column .= '《'.$course['title'].'》'.',';
            $column .= $order['totalPrice'].',';

            if (!empty($order['coupon'])) {
                $column .= $order['coupon'].',';
            } else {
                $column .= '无'.',';
            }

            $column .= $order['couponDiscount'].',';
            $column .= $order['coinRate'] ? ($order['coinAmount'] / $order['coinRate']).',' : '0,';
            $column .= $order['amount'].',';
            $column .= $payment[$order['payment']].',';
            $column .= $users[$order['userId']]['nickname'].',';
            $column .= $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'].',' : '-'.',';

            if (preg_match('/管理员添加/', $order['title'])) {
                $column .= '管理员添加,';
            } else {
                $column .= '-,';
            }

            $column .= date('Y-n-d H:i:s', $order['createdTime']).',';

            if (0 != $order['paidTime']) {
                $column .= date('Y-n-d H:i:s', $order['paidTime']);
            } else {
                $column .= '-';
            }

            $results[] = $column;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239).chr(187).chr(191).$str;

        $filename = sprintf('%s-订单-(%s).csv', $course['title'], date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public function taskLearnDetailAction(Request $request, $courseSetId, $courseId, $taskId)
    {
        $students = array();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        $count = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($taskId, 'all');
        $paginator = new Paginator($request, $count, 20);

        $results = $this->getTaskResultService()->searchTaskResults(
            array('courseId' => $courseId, 'activityId' => $task['activityId']),
            array('createdTime' => 'ASC'),
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
            array(
                'task' => $task,
                'paginator' => $paginator,
                'students' => $students,
            )
        );
    }

    public function questionMarkerStatsAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $taskId = $request->query->get('taskId', 0);

        $stats = $this->getMarkerReportService()->statTaskQuestionMarker($courseId, $taskId);
        $this->sortMarkerStats($stats, $request);

        return $this->render('course-manage/question-marker/stats.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'stats' => $stats,
        ));
    }

    public function questionMarkerAnalysisAction(Request $request, $courseSetId, $courseId, $questionMarkerId)
    {
        $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $taskId = $request->query->get('taskId');
        $analysis = $this->getMarkerReportService()->analysisQuestionMarker($courseId, $taskId, $questionMarkerId);

        return $this->render('course-manage/question-marker/analysis.html.twig', array(
            'analysis' => $analysis,
        ));
    }

    public function showPublishAction(Request $request, $courseId)
    {
        $status = $request->request->get('status', 1);
        $this->getCourseService()->changeShowPublishLesson($courseId, $status);

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
            if ($activity['ext']['replayStatus'] == 'videoGenerated') {
                return $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
            } else {
                return array();
            }
        }

        return array();
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
}
