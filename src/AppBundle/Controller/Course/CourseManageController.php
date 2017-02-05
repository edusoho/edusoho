<?php
namespace AppBundle\Controller\Course;

use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Biz\Util\EdusohoLiveClient;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Order\Service\OrderService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Course\Service\ThreadService;
use Biz\Task\Strategy\StrategyContext;
use Biz\Task\Service\TaskResultService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseNoteService;
use Biz\Testpaper\Service\TestpaperService;
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
            $this->getCourseService()->createCourse($data);

            return $this->redirect($this->generateUrl('course_set_manage_courses', array('courseSetId' => $courseSetId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        return $this->render('course-manage/create-modal.html.twig', array(
            'courseSet' => $courseSet
        ));
    }

    public function copyAction(Request $request, $courseSetId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseService()->copyCourse($data);

            return $this->redirect($this->generateUrl('course_set_manage_courses', array('courseSetId' => $courseSetId)));
        }

        $courseId  = $request->query->get('courseId');
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render('course-manage/create-modal.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function listAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        if ($courseSet['type'] == 'live') {
            $course = current($courses);
            return $this->redirectToRoute('course_set_manage_course_tasks', array(
                'courseSetId' => $courseSet['id'],
                'courseId'    => $course['id']
            ));
        }

        return $this->render('courseset-manage/courses.html.twig', array(
            'courseSet' => $courseSet,
            'courses'   => $courses
        ));
    }

    public function tasksAction(Request $request, $courseSetId, $courseId)
    {
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        $tasks = $this->getTaskService()->findTasksByCourseId($courseId);

        $files = $this->prepareTaskActivityFiles($tasks);

        $courseItems = $this->getCourseService()->findCourseItems($courseId);
        $taskPerDay  = $this->getFinishedTaskPerDay($course, $tasks);

        return $this->render($this->getTasksTemplate($course), array(
            'taskNum'    => count($tasks),
            'files'      => $files,
            'courseSet'  => $courseSet,
            'course'     => $course,
            'items'      => $courseItems,
            'taskPerDay' => $taskPerDay
        ));
    }

    protected function getTasksTemplate($course)
    {
        if ($course['isDefault']) {
            return 'course-manage/free-mode/tasks.html.twig';
        } else {
            return 'course-manage/lock-mode/tasks.html.twig';
        }
    }

    protected function getFinishedTaskPerDay($course, $tasks)
    {
        $taskNum = count($tasks);
        if ($course['expiryMode'] == 'days') {
            $finishedTaskPerDay = empty($course['expiryDays']) ? false : $taskNum / $course['expiryDays'];
        } else {
            $diffDay            = ($course['expiryEndDate'] - $course['expiryStartDate']) / (24 * 60 * 60);
            $finishedTaskPerDay = empty($diffDay) ? false : $taskNum / $diffDay;
        }
        return round($finishedTaskPerDay, 0);
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->get('biz'));
    }

    public function infoAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (!empty($data['goals'])) {
                $data['goals'] = json_decode($data['goals'], true);
            }
            if (!empty($data['audiences'])) {
                $data['audiences'] = json_decode($data['audiences'], true);
            }
            $this->getCourseService()->updateCourse($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_info', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        return $this->render('course-manage/info.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $this->formatCourseDate($course)
        ));
    }

    public function marketingAction(Request $request, $courseSetId, $courseId)
    {
        $freeTasks = $this->getTaskService()->findFreeTasksByCourseId($courseId);
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data['enableBuyExpiryTime'])) {
                unset($data['buyExpiryTime']);
            }

            if (!empty($data['services'])) {
                $data['services'] = json_decode($data['services'], true);
            }
            if (!empty($data['freeTaskIds'])) {
                $canFreeTaskIds = $data['freeTaskIds'];
                $freeTaskIds    = ArrayToolkit::column($freeTasks, 'id');
                $this->getTaskService()->updateTasks($freeTaskIds, array('isFree' => 0));
                $this->getTaskService()->updateTasks($canFreeTaskIds, array('isFree' => 1));
                unset($data['freeTaskIds']);
            }

            $this->getCourseService()->updateCourseMarketing($courseId, $data);

            return $this->redirect($this->generateUrl('course_set_manage_course_marketing', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $conditions       = array(
            'courseId' => $courseId,
            'types'    => array('text', 'video', 'audio', 'flash', 'doc', 'ppt')
        );
        $canFreeTaskCount = $this->getTaskService()->count($conditions);
        $canFreeTasks     = $this->getTaskService()->search($conditions, array('seq' => 'ASC'), 0, $canFreeTaskCount);

        return $this->render('course-manage/marketing.html.twig', array(
            'courseSet'    => $courseSet,
            'course'       => $course,
            'canFreeTasks' => $canFreeTasks,
            'freeTasks'    => $freeTasks
        ));
    }

    public function teachersAction(Request $request, $courseSetId, $courseId)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (empty($data) || !isset($data['teachers'])) {
                throw new InvalidArgumentException('Empty Data');
            }
            $teachers = json_decode($data['teachers'], true);

            $this->getCourseMemberService()->setCourseTeachers($courseId, $teachers);

            return $this->redirect($this->generateUrl('course_set_manage_course_teachers', array('courseSetId' => $courseSetId, 'courseId' => $courseId)));
        }

        $courseSet  = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course     = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $teachers   = $this->getCourseService()->findTeachersByCourseId($courseId);
        $teacherIds = array();
        if (!empty($teachers)) {
            foreach ($teachers as $teacher) {
                $teacherIds[] = array(
                    'id'        => $teacher['userId'],
                    'isVisible' => $teacher['isVisible'],
                    'nickname'  => $teacher['nickname'],
                    'avatar'    => $this->get('topxia.twig.web_extension')->getFilePath($teacher['smallAvatar'], 'avatar.png')
                );
            }
        }
        return $this->render('course-manage/teachers.html.twig', array(
            'courseSet'  => $courseSet,
            'course'     => $course,
            'teacherIds' => $teacherIds
        ));
    }

    public function teachersMatchAction(Request $request, $courseSetId, $courseId)
    {
        $queryField = $request->query->get('q');
        $users      = $this->getUserService()->searchUsers(
            array('nickname' => $queryField, 'roles' => 'ROLE_TEACHER'),
            array('createdTime' => 'DESC'),
            0,
            10
        );

        $teachers = array();

        foreach ($users as $user) {
            $teachers[] = array(
                'id'        => $user['id'],
                'nickname'  => $user['nickname'],
                'avatar'    => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png'),
                'isVisible' => 1
            );
        }

        return $this->createJsonResponse($teachers);
    }

    public function closeCheckAction(Request $request, $courseSetId, $courseId)
    {
        $course           = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);
        $publishedCourses = $this->getCourseService()->findPublishedCoursesByCourseSetId($courseSetId);
        if (count($publishedCourses) == 1) {
            return $this->createJsonResponse(array('warn' => true, 'message' => "{$course['title']}是课程下唯一发布的教学计划，如果关闭则所在课程也会被关闭。"));
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
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function publishAction(Request $request, $courseSetId, $courseId)
    {
        try {
            $this->getCourseService()->publishCourse($courseId);
            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function courseItemsSortAction(Request $request, $courseId)
    {
        $ids = $request->request->get("ids");
        $this->getCourseService()->sortCourseItems($courseId, $ids);
        return $this->createJsonResponse(array('result' => true));
    }

    /**
     * @param  $tasks
     *
     * @return array
     */
    public function prepareTaskActivityFiles($tasks)
    {
        $tasks       = ArrayToolkit::index($tasks, 'id');
        $activityIds = ArrayToolkit::column($tasks, 'activityId');

        $activities = $this->getActivityService()->findActivities($activityIds, $fetchMedia = true);

        $files = array();
        array_walk($activities, function ($activity) use (&$files) {
            if (in_array($activity['mediaType'], array('video', 'audio', 'doc'))) {
                $files[$activity['id']] = empty($activity['ext']['file']) ? null : $activity['ext']['file'];
            }
        });
        return $files;
    }

    public function ordersAction(Request $request, $courseSetId, $courseId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $courseSetting = $this->setting("course");

        if (!$this->getCurrentUser()->isAdmin() && (empty($courseSetting["teacher_search_order"]) || $courseSetting["teacher_search_order"] != 1)) {
            throw $this->createAccessDeniedException('查询订单已关闭，请联系管理员');
        }

        $conditions               = $request->query->all();
        $type                     = 'course';
        $conditions['targetType'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $conditions['targetId'] = $courseId;

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime']   = strtotime($conditions['endDateTime']);
        }

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countOrders($conditions),
            10
        );

        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));

        foreach ($orders as $index => $expiredOrderToBeUpdated) {
            if ((($expiredOrderToBeUpdated["createdTime"] + 48 * 60 * 60) < time()) && ($expiredOrderToBeUpdated["status"] == 'created')) {
                $this->getOrderService()->cancelOrder($expiredOrderToBeUpdated['id']);
                $orders[$index]['status'] = 'cancelled';
            }
        }

        return $this->render('course-manage/orders.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course,
            'request'   => $request,
            'orders'    => $orders,
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function ordersExportCsvAction(Request $request, $courseSetId, $courseId)
    {
        // $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        $courseSetting = $this->setting("course");

        if (!$this->getCurrentUser()->isAdmin() && (empty($courseSetting["teacher_search_order"]) || $courseSetting["teacher_search_order"] != 1)) {
            throw $this->createAccessDeniedException('查询订单已关闭，请联系管理员');
        }

        $status  = array(
            'created'   => '未付款',
            'paid'      => '已付款',
            'refunding' => '退款中',
            'refunded'  => '已退款',
            'cancelled' => '已关闭'
        );
        $payment = array(
            'alipay'  => '支付宝',
            'wxpay'   => '微信支付',
            'cion'    => '虚拟币支付',
            'outside' => '站外支付',
            'none'    => '--'
        );

        $conditions = $request->query->all();

        $type                     = 'course';
        $conditions['targetType'] = $type;

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
        }

        $conditions['targetId'] = $courseId;

        if (!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])) {
            $conditions['startTime'] = strtotime($conditions['startDateTime']);
            $conditions['endTime']   = strtotime($conditions['endDateTime']);
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
            $column = "";
            $column .= $order['sn'] . ",";
            $column .= $status[$order['status']] . ",";
            $column .= $order['title'] . ",";
            $column .= "《" . $course['title'] . "》" . ",";
            $column .= $order['totalPrice'] . ",";

            if (!empty($order['coupon'])) {
                $column .= $order['coupon'] . ",";
            } else {
                $column .= '无' . ",";
            }

            $column .= $order['couponDiscount'] . ",";
            $column .= $order['coinRate'] ? ($order['coinAmount'] / $order['coinRate']) . "," : '0,';
            $column .= $order['amount'] . ",";
            $column .= $payment[$order['payment']] . ",";
            $column .= $users[$order['userId']]['nickname'] . ",";
            $column .= $profiles[$order['userId']]['truename'] ? $profiles[$order['userId']]['truename'] . "," : "-" . ",";

            if (preg_match('/管理员添加/', $order['title'])) {
                $column .= '管理员添加,';
            } else {
                $column .= "-,";
            }

            $column .= date('Y-n-d H:i:s', $order['createdTime']) . ",";

            if ($order['paidTime'] != 0) {
                $column .= date('Y-n-d H:i:s', $order['paidTime']);
            } else {
                $column .= "-";
            }

            $results[] = $column;
        }

        $str .= implode("\r\n", $results);
        $str = chr(239) . chr(187) . chr(191) . $str;

        $filename = sprintf("%s-订单-(%s).csv", $course['title'], date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public function dashboardAction(Request $request, $courseSetId, $courseId)
    {
        $tab = $request->query->get('tab', 'course');

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);
        $course    = $this->getCourseService()->tryManageCourse($courseId, $courseSetId);

        switch ($tab) {
            case 'course':
                return $this->renderDashboardForCourse($course, $courseSet);
            case 'task':
                return $this->renderDashboardForTasks($course, $courseSet);
            case 'task-detail':
                return $this->renderDashboardForTaskDetails($course, $courseSet);
            default:
                throw new InvalidArgumentException("Unknown tab#{$tab}");
        }
    }

    public function taskLearnDetailAction(Request $request, $courseSetId, $courseId, $taskId)
    {
        $students = array();
        $task     = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        // $count     = $this->getCourseService()->searchLearnCount(array('courseId' => $courseId, 'lessonId' => $lessonId));
        $count     = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($taskId, 'all');
        $paginator = new Paginator($request, $count, 20);

        $results = $this->getTaskResultService()->searchTaskResults(array('courseId' => $courseId, 'activityId' => $task['activityId']), array('createdTime' => 'ASC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        foreach ($results as $key => $result) {
            $user                           = $this->getUserService()->getUser($result['userId']);
            $students[$key]['nickname']     = $user['nickname'];
            $students[$key]['startTime']    = $result['createdTime'];
            $students[$key]['finishedTime'] = $result['finishedTime'];
            $students[$key]['learnTime']    = $result['time'];
            $students[$key]['watchTime']    = $result['time'];

            if ($activity['mediaType'] == 'testpaper') {
                $paperId     = $activity['mediaId'];
                $paperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $paperId, $courseId, $activity['id'], 'testpaper');

                $students[$key]['result'] = empty($paperResult) ? 0 : $paperResult['score'];
            }
        }

        $task['length'] = intval($activity['length']);

        return $this->render('course-manage/dashboard/task-detail-modal.html.twig', array(
            'task'      => $task,
            'paginator' => $paginator,
            'students'  => $students
        ));
    }

    private function _canRecord($liveId)
    {
        $client = new EdusohoLiveClient();
        return $client->isAvailableRecord($liveId);
    }

    protected function renderDashboardForCourse($course, $courseSet)
    {
        $summary             = $this->getReportService()->summary($course['id']);
        $lateMonthLearndData = $this->getReportService()->getLateMonthLearndData($course['id']);

        return $this->render('course-manage/dashboard/course.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'summary'       => $summary,
            'studentNum'    => ArrayToolkit::column($lateMonthLearndData, 'studentNum'),
            'finishedNum'   => ArrayToolkit::column($lateMonthLearndData, 'finishedNum'),
            'finishedRate'  => ArrayToolkit::column($lateMonthLearndData, 'finishedRate'),
            'noteNum'       => ArrayToolkit::column($lateMonthLearndData, 'noteNum'),
            'askNum'        => ArrayToolkit::column($lateMonthLearndData, 'askNum'),
            'discussionNum' => ArrayToolkit::column($lateMonthLearndData, 'discussionNum'),
            'days'          => ArrayToolkit::column($lateMonthLearndData, 'day')
        ));
    }

    protected function renderDashboardForTasks($course, $courseSet)
    {
        $taskStat = $this->getReportService()->getCourseTaskLearnStat($course['id']);
        return $this->render('course-manage/dashboard/task.html.twig', array(
            'courseSet'    => $courseSet,
            'course'       => $course,
            'taskTitles'   => ArrayToolkit::column($taskStat, 'alias'),
            'finishedRate' => ArrayToolkit::column($taskStat, 'finishedRate'),
            'finishedNum'  => ArrayToolkit::column($taskStat, 'finishedNum'),
            'learnNum'     => ArrayToolkit::column($taskStat, 'learnNum')
        ));
    }

    protected function renderDashboardForTaskDetails($course, $courseSet)
    {
        $isLearnedNum = $this->getCourseMemberService()->countMembers(array('isLearned' => 1, 'courseId' => $course['id']));

        $learnTime = $this->getActivityLearnLogService()->sumLearnTime(array('courseId' => $course['id']));
        $learnTime = $course["studentNum"] == 0 ? 0 : intval($learnTime / $course["studentNum"]);

        $noteCount = $this->getNoteService()->countCourseNotes(array('courseId' => $course['id']));

        $questionCount = $this->getThreadService()->countThreads(array('courseId' => $course['id'], 'type' => 'question'));

        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($course['id']);

        foreach ($tasks as $key => $value) {
            $taskLearnedNum = $this->getTaskResultService()->countLearnNumByTaskId($value['id']);

            $finishedNum = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($value['id'], 'finish');

            $taskLearnTime = $this->getActivityLearnLogService()->sumLearnTime(array('taskId' => $value['id']));
            $taskLearnTime = $taskLearnedNum == 0 ? 0 : intval($taskLearnTime / $taskLearnedNum);

            $taskWatchTime = $this->getActivityLearnLogService()->sumLearnTime(array('taskId' => $value['id']));
            $taskWatchTime = $taskLearnedNum == 0 ? 0 : intval($taskWatchTime / $taskLearnedNum);

            $tasks[$key]['LearnedNum']  = $taskLearnedNum;
            $tasks[$key]['length']      = intval($tasks[$key]['activity']['length']);
            $tasks[$key]['type']        = $tasks[$key]['activity']['mediaType'];
            $tasks[$key]['finishedNum'] = $finishedNum;
            $tasks[$key]['learnTime']   = $taskLearnTime;
            $tasks[$key]['watchTime']   = $taskWatchTime;

            if ($value['type'] == 'testpaper') {
                $paperId  = $value['activity']['mediaId'];
                $score    = $this->getTestpaperService()->searchTestpapersScore(array('testId' => $paperId));
                $paperNum = $this->getTestpaperService()->searchTestpaperResultsCount(array('testId' => $paperId));

                $tasks[$key]['score'] = ($finishedNum == 0 || $paperNum == 0) ? 0 : intval($score / $paperNum);
            }
        }

        return $this->render('course-manage/dashboard/task-learn.html.twig', array(
            'courseSet'     => $courseSet,
            'course'        => $course,
            'isLearnedNum'  => $isLearnedNum,
            'learnTime'     => $learnTime,
            'noteCount'     => $noteCount,
            'questionCount' => $questionCount,
            'tasks'         => $tasks
        ));
    }

    protected function _getLiveReplayMedia(array $task)
    {
        if ($task['type'] == 'live') {
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
        if (isset($course['expiryStartDate'])) {
            $course['expiryStartDate'] = date('Y-m-d', $course['expiryStartDate']);
        }
        if (isset($course['expiryEndDate'])) {
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
     * @return \Topxia\WebBundle\Twig\Extension\WebExtension
     */
    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
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
     * @return OrderService
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
}
