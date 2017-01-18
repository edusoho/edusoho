<?php
namespace AppBundle\Controller\Classroom;

use Topxia\Common\Paginator;
use Topxia\Common\ExportHelp;
use Topxia\Common\ArrayToolkit;
use Vip\Service\Vip\LevelService;
use Topxia\Common\SimpleValidator;
use Biz\Order\Service\OrderService;
use Biz\Content\Service\FileService;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Service\CourseService;
use Biz\Thread\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use AppBundle\Controller\BaseController;
use Biz\User\Service\NotificationService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Homework\Service\Homework\HomeworkService;
use Symfony\Component\HttpFoundation\Response;
use Biz\Classroom\Service\ClassroomReviewService;
use Topxia\WebBundle\Twig\Extension\WebExtension;

class ClassroomManageController extends BaseController
{
    public function indexAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        $courses   = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
//        $courseIds = ArrayToolkit::column($courses, 'id');

        $currentTime    = time();
        $todayTimeStart = strtotime(date("Y-m-d", $currentTime));
        $todayTimeEnd   = strtotime(date("Y-m-d", $currentTime + 24 * 3600));

        $yesterdayTimeStart = strtotime(date("Y-m-d", $currentTime - 24 * 3600));
        $yesterdayTimeEnd   = strtotime(date("Y-m-d", $currentTime));

        //是否可以直接根据courseId查询，取决于classroom对course是【引用】还是【复制】
        //TODO
        $todayFinishedLessonNum     = 0;
        $yesterdayFinishedLessonNum = 0;
//        $todayFinishedLessonNum     = $this->getCourseService()->searchLearnCount(array("targetType" => "classroom", "courseIds" => $courseIds, "startTime" => $todayTimeStart, "endTime" => $todayTimeEnd, "status" => "finished"));
        //        $yesterdayFinishedLessonNum = $this->getCourseService()->searchLearnCount(array("targetType" => "classroom", "courseIds" => $courseIds, "startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd, "status" => "finished"));

        $todayThreadCount     = $this->getThreadService()->searchThreadCount(array('targetType' => 'classroom', 'targetId' => $id, 'type' => 'discussion', "startTime" => $todayTimeStart, "endTime" => $todayTimeEnd, "status" => "open"));
        $yesterdayThreadCount = $this->getThreadService()->searchThreadCount(array('targetType' => 'classroom', 'targetId' => $id, 'type' => 'discussion', "startTime" => $yesterdayTimeStart, "endTime" => $yesterdayTimeEnd, "status" => "open"));

        $studentCount = $this->getClassroomService()->searchMemberCount(array('role' => 'student', 'classroomId' => $id, 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));
        $auditorCount = $this->getClassroomService()->searchMemberCount(array('role' => 'auditor', 'classroomId' => $id, 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));

        $allCount = $studentCount + $auditorCount;

        $yestodayStudentCount = $this->getClassroomService()->searchMemberCount(array('role' => 'student', 'classroomId' => $id, 'startTimeLessThan' => strtotime(date('Y-m-d')), 'startTimeGreaterThan' => (strtotime(date('Y-m-d')) - 24 * 3600)));
        $yestodayAuditorCount = $this->getClassroomService()->searchMemberCount(array('role' => 'auditor', 'classroomId' => $id, 'startTimeLessThan' => strtotime(date('Y-m-d')), 'startTimeGreaterThan' => (strtotime(date('Y-m-d')) - 24 * 3600)));

        $yestodayAllCount = $yestodayStudentCount + $yestodayAuditorCount;

        $reviewsNum = $this->getClassroomReviewService()->searchReviewCount(array('classroomId' => $id));
        $paginator  = new Paginator(
            $this->get('request'),
            $reviewsNum,
            20
        );

        $reviews = $this->getClassroomReviewService()->searchReviews(
            array('classroomId' => $id),
            array('createdTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds     = ArrayToolkit::column($reviews, 'userId');
        $reviewUsers = $this->getUserService()->findUsersByIds($userIds);
        return $this->render("classroom-manage/index.html.twig", array(
            'classroom'                  => $classroom,
            'studentCount'               => $studentCount,
            'yestodayStudentCount'       => $yestodayStudentCount,
            'allCount'                   => $allCount,
            'yestodayAllCount'           => $yestodayAllCount,
            'reviews'                    => $reviews,
            'reviewUsers'                => $reviewUsers,
            'todayFinishedLessonNum'     => $todayFinishedLessonNum,
            'yesterdayFinishedLessonNum' => $yesterdayFinishedLessonNum,
            'todayThreadCount'           => $todayThreadCount,
            'yesterdayThreadCount'       => $yesterdayThreadCount
        ));
    }

    public function menuAction(Request $request, $classroom, $sideNav, $context)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        $canManage = $this->getClassroomService()->canManageClassroom($classroom['id']);
        $canHandle = $this->getClassroomService()->canHandleClassroom($classroom['id']);

        return $this->render('classroom-manage/menu.html.twig', array(
            'canManage' => $canManage,
            'canHandle' => $canHandle,
            'side_nav'  => $sideNav,
            'classroom' => $classroom,
            '_context'  => $context
        ));
    }

    public function studentsAction(Request $request, $id, $role = 'student')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields    = $request->query->all();
        $condition = array();

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserIds($fields['keyword']);
        }

        $condition = array_merge($condition, array('classroomId' => $id, 'role' => 'student'));

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users          = $this->getUserService()->findUsersByIds($studentUserIds);

        $progresses = array();

        foreach ($students as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($classroom, $student);
        }

        return $this->render("classroom-manage/student.html.twig", array(
            'classroom'  => $classroom,
            'students'   => $students,
            'users'      => $users,
            'progresses' => $progresses,
            'paginator'  => $paginator,
            'role'       => $role
        ));
    }

    public function aduitorAction(Request $request, $id, $role = 'auditor')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields    = $request->query->all();
        $condition = array();

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserIds($fields['keyword']);
        }

        $condition = array_merge($condition, array('classroomId' => $id, 'role' => 'auditor'));

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($condition),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users          = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render("classroom-manage/auditor.html.twig", array(
            'classroom' => $classroom,
            'students'  => $students,
            'users'     => $users,
            'paginator' => $paginator,
            'role'      => $role
        ));
    }

    public function refundRecordAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();

        $condition = array();

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserIds($fields['keyword']);
        }

        $condition['targetId']   = $id;
        $condition['targetType'] = 'classroom';
        $condition['status']     = 'success';

        $paginator = new Paginator(
            $request,
            $this->getOrderService()->countRefunds($condition),
            20
        );

        $refunds = $this->getOrderService()->searchRefunds(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($refunds, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);
        $users   = ArrayToolkit::index($users, "id");

        $orderIds = ArrayToolkit::column($refunds, 'orderId');
        $orders   = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders   = ArrayToolkit::index($orders, "id");

        foreach ($refunds as $key => $refund) {
            if (isset($users[$refund['userId']])) {
                $refunds[$key]['user'] = $users[$refund['userId']];
            }

            if (isset($orders[$refund['orderId']])) {
                $refunds[$key]['order'] = $orders[$refund['orderId']];
            }
        }

        return $this->render("classroom-manage/quit-record.html.twig", array(
            'classroom' => $classroom,
            'paginator' => $paginator,
            'refunds'   => $refunds
        ));
    }

    public function remarkAction(Request $request, $classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);

        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user      = $this->getUserService()->getUser($userId);
        $member    = $this->getClassroomService()->getClassroomMember($classroomId, $userId);

        if ('POST' == $request->getMethod()) {
            $data   = $request->request->all();
            $member = $this->getClassroomService()->remarkStudent($classroom['id'], $user['id'], $data['remark']);

            return $this->createStudentTrResponse($classroom, $member);
        }

        return $this->render('classroom-manage/remark-modal.html.twig', array(
            'member'    => $member,
            'user'      => $user,
            'classroom' => $classroom
        ));
    }

    private function createStudentTrResponse($classroom, $student)
    {
        $this->getClassroomService()->tryManageClassroom($classroom["id"]);

        $user     = $this->getUserService()->getUser($student['userId']);
        $progress = $this->calculateUserLearnProgress($classroom, $student);

        return $this->render('classroom-manage/tr.html.twig', array(
            'classroom' => $classroom,
            'student'   => $student,
            'user'      => $user,
            'role'      => $student["role"],
            'progress'  => $progress
        ));
    }

    public function removeAction($classroomId, $userId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $condition = array(
            'targetType' => 'classroom',
            'targetId'   => $classroomId,
            'userId'     => $userId,
            'status'     => 'paid'
        );
        $orders = $this->getOrderService()->searchOrders($condition, 'latest', 0, 1);

        $order = array();
        foreach ($orders as $key => $value) {
            $order[$key] = $value;
        }

        $this->getClassroomService()->removeStudent($classroomId, $userId);

        $reason = array(
            'type'     => 'other',
            'note'     => '"'.$user['nickname'].'"'.' 手动移除',
            'operator' => $user['id']
        );
        $this->getOrderService()->applyRefundOrder($order['id'], null, $reason);
        $message = array(
            'classroomId'    => $classroom['id'],
            'classroomTitle' => $classroom['title'],
            'userId'         => $user['id'],
            'userName'       => $user['nickname'],
            'type'           => 'remove');
        $this->getNotificationService()->notify($userId, 'classroom-student', $message);

        return $this->createJsonResponse(true);
    }

    public function createAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            $user = $this->getUserService()->getUserByLoginField($data['queryfield']);

            if (empty($user)) {
                throw $this->createNotFoundException("用户{$data['nickname']}不存在");
            }

            if ($this->getClassroomService()->isClassroomStudent($classroom['id'], $user['id'])) {
                throw $this->createNotFoundException('用户已经是学员，不能添加！');
            }

            $classroomSetting = $this->getSettingService()->get('classroom');

            $classroomName = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

            if (empty($data['price'])) {
                $data['price'] = 0;
            }

            $order = $this->getOrderService()->createOrder(array(
                'userId'     => $user['id'],
                'title'      => "购买{$classroomName}《{$classroom['title']}》(管理员添加)",
                'targetType' => 'classroom',
                'targetId'   => $classroom['id'],
                'amount'     => $data['price'],
                'payment'    => 'outside',
                'snPrefix'   => 'CR',
                'totalPrice' => $classroom['price']
            ));

            $this->getOrderService()->payOrder(array(
                'sn'       => $order['sn'],
                'status'   => 'success',
                'amount'   => $order['amount'],
                'paidTime' => time()
            ));

            $info = array(
                'orderId' => $order['id'],
                'note'    => $data['remark']
            );
            $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);

            $member      = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);
            $currentUser = $this->getCurrentUser();
            $message     = array(
                'classroomId'    => $classroom['id'],
                'classroomTitle' => $classroom['title'],
                'userId'         => $currentUser['id'],
                'userName'       => $currentUser['nickname'],
                'type'           => 'create');

            $this->getNotificationService()->notify($member['userId'], 'classroom-student', $message);

            $this->getLogService()->info('classroom', 'add_student', "班级《{$classroom['title']}》(#{$classroom['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}");

            return $this->createStudentTrResponse($classroom, $member);
        }

        return $this->render('classroom-manage/create-modal.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function checkStudentAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $keyWord = $request->query->get('value');
        $user    = $this->getUserService()->getUserByLoginField($keyWord);

        if (!$user) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($id, $user['id']);

            if ($isClassroomStudent) {
                $response = array('success' => false, 'message' => '该用户已是本班级的学员了');
            } else {
                $response = array('success' => true, 'message' => '');
            }
        }

        return $this->createJsonResponse($response);
    }

    public function exportDatasAction(Request $request, $id, $role)
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $students, $classroomMemberCount) = $this->getExportContent($id, $role, $start, $limit, $exportAllowCount);

        $file = '';
        if ($start == 0) {
            $file = ExportHelp::addFileTitle($request, 'classroom_'.$role.'_students', $title);
        }

        $content = implode("\r\n", $students);
        $file    = ExportHelp::saveToTempFile($request, $content, $file);
        $status  = ExportHelp::getNextMethod($start + $limit, $classroomMemberCount);

        return $this->createJsonResponse(
            array(
                'status'   => $status,
                'fileName' => $file,
                'start'    => $start + $limit
            )
        );
    }

    public function exportCsvAction(Request $request, $id)
    {
        $role     = $request->query->get('role');
        $fileName = sprintf("classroom-%s-%s-(%s).csv", $id, $role, date('Y-n-d'));
        return ExportHelp::exportCsv($request, $fileName);
    }

    private function getExportContent($id, $role, $start, $limit, $exportAllowCount)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $gender = array('female' => '女', 'male' => '男', 'secret' => '秘密');

        $classroom = $this->getClassroomService()->getClassroom($id);

        $userinfoFields = array('truename', 'job', 'mobile', 'qq', 'company', 'gender', 'idcard', 'weixin');

        if ($role == 'student') {
            $condition = array(
                'classroomId' => $classroom['id'],
                'role'        => 'student'
            );
        } else {
            $condition = array(
                'classroomId' => $classroom['id'],
                'role'        => 'auditor'
            );
        }
        $classroomMemberCount = $this->getClassroomService()->searchMemberCount($condition);
        $classroomMemberCount = ($classroomMemberCount > $exportAllowCount) ? $exportAllowCount : $classroomMemberCount;
        if ($classroomMemberCount < ($start + $limit + 1)) {
            $limit = $classroomMemberCount - $start;
        }
        $classroomMembers = $this->getClassroomService()->searchMembers(
            $condition,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        $fields['weibo'] = '微博';

        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $userinfoFields = array_flip($userinfoFields);

        $fields = array_intersect_key($fields, $userinfoFields);

        $studentUserIds = ArrayToolkit::column($classroomMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $progresses = array();

        foreach ($classroomMembers as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($classroom, $student);
        }

        $str = '用户名,Email,加入学习时间,学习进度,姓名,性别,QQ号,微信号,手机号,公司,职业,头衔';

        foreach ($fields as $key => $value) {
            $str .= ",".$value;
        }

        $students = array();

        foreach ($classroomMembers as $classroomMember) {
            $member = "";
            $member .= $users[$classroomMember['userId']]['nickname'].",";
            $member .= $users[$classroomMember['userId']]['email'].",";
            $member .= date('Y-n-d H:i:s', $classroomMember['createdTime']).",";
            $member .= $progresses[$classroomMember['userId']]['percent'].",";
            $member .= $profiles[$classroomMember['userId']]['truename'] ? $profiles[$classroomMember['userId']]['truename']."," : "-".",";
            $member .= $gender[$profiles[$classroomMember['userId']]['gender']].",";
            $member .= $profiles[$classroomMember['userId']]['qq'] ? $profiles[$classroomMember['userId']]['qq']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['weixin'] ? $profiles[$classroomMember['userId']]['weixin']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['mobile'] ? $profiles[$classroomMember['userId']]['mobile']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['company'] ? $profiles[$classroomMember['userId']]['company']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['job'] ? $profiles[$classroomMember['userId']]['job']."," : "-".",";
            $member .= $users[$classroomMember['userId']]['title'] ? $users[$classroomMember['userId']]['title']."," : "-".",";

            foreach ($fields as $key => $value) {
                $member .= $profiles[$classroomMember['userId']][$key] ? $profiles[$classroomMember['userId']][$key]."," : "-".",";
            }

            $students[] = $member;
        }
        return array($str, $students, $classroomMemberCount);
    }

    public function serviceAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if (!$this->isPluginInstalled('ClassroomPlan') && $classroom['service'] && in_array('studyPlan', $classroom['service'])) {
            unset($classroom['service']['studyPlan']);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $data['service'] = empty($data['service']) ? null : $data['service'];

            $classroom = $this->getClassroomService()->updateClassroom($id, $data);
            $this->setFlashMessage('success', '保存成功！');
        }

        return $this->render('classroom-manage/services.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function teachersAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = array();

        if ($request->getMethod() == "POST") {
            $data = $request->request->all();

            if (isset($data['teacherIds'])) {
                $teacherIds = $data['teacherIds'];

                $fields = array('teacherIds' => $teacherIds);
            }

            if (isset($data['headTeacherId'])) {
                $fields['headTeacherId'] = $data['headTeacherId'];
                $this->getClassroomService()->addHeadTeacher($id, $fields['headTeacherId']);
            }

            if ($fields) {
                $classroom = $this->getClassroomService()->updateClassroom($id, $fields);
            }

            $this->setFlashMessage('success', '保存成功！');
        }

        $teacherIds = $this->getClassroomService()->findTeachers($id);
        $teachers   = $this->getUserService()->findUsersByIds($teacherIds);

        $teacherItems = array();

        foreach ($teacherIds as $key => $teacherId) {
            $user           = $teachers[$teacherId];
            $teacherItems[] = array(
                'id'       => $user['id'],
                'nickname' => $user['nickname'],
                'avatar'   => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png')
            );
        }

        $headTeacher = $this->getUserService()->getUser($classroom['headTeacherId']);

        return $this->render("classroom-manage/teachers.html.twig", array(
            'classroom'    => $classroom,
            'teachers'     => $teachers,
            'teacherIds'   => $teacherIds,
            'headTeacher'  => $headTeacher,
            'teacherItems' => $teacherItems
        ));
    }

    public function headteacherAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        if ($request->getMethod() == "POST") {
            $data          = $request->request->all();
            $headTeacherId = empty($data['ids']) ? 0 : $data['ids'][0];
            $this->getClassroomService()->addHeadTeacher($id, $headTeacherId);

            $this->setFlashMessage('success', '保存成功！');
        }

        $classroom      = $this->getClassroomService()->getClassroom($id);
        $headTeacher    = $this->getUserService()->getUser($classroom['headTeacherId']);
        $newheadTeacher = array();

        if ($headTeacher) {
            $newheadTeacher[] = array(
                'id'       => $headTeacher['id'],
                'nickname' => $headTeacher['nickname'],
                'avatar'   => $this->getWebExtension()->getFilePath($headTeacher['smallAvatar'], 'avatar.png')
            );
        }

        return $this->render("classroom-manage/headteacher.html.twig", array(
            'classroom'   => $classroom,
            'headTeacher' => $newheadTeacher
        ));
    }

    public function assistantsAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($request->getMethod() == "POST") {
            $data    = $request->request->all();
            $userIds = empty($data['ids']) ? array() : $data['ids'];
            $this->getClassroomService()->updateAssistants($id, $userIds);

            if ($userIds) {
                $fields = array('assistantIds' => $userIds);

                $classroom = $this->getClassroomService()->updateClassroom($id, $fields);
            }

            $this->setFlashMessage('success', '保存成功！');
        }

        $assistantIds     = $this->getClassroomService()->findAssistants($id);
        $users            = $this->getUserService()->findUsersByIds($assistantIds);
        $sortedAssistants = array();

        foreach ($assistantIds as $key => $assistantId) {
            $user               = $users[$assistantId];
            $sortedAssistants[] = array(
                'id'       => $user['id'],
                'nickname' => $user['nickname'],
                'avatar'   => $this->getWebExtension()->getFilePath($user['smallAvatar'], 'avatar.png')
            );
        }

        return $this->render("classroom-manage/assistants.html.twig", array(
            'classroom'  => $classroom,
            'assistants' => $sortedAssistants
        ));
    }

    public function setInfoAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($request->getMethod() == "POST") {
            $class           = $request->request->all();
            $class['tagIds'] = $this->getTagIdsFromRequest($request);

            $classroom = $this->getClassroomService()->updateClassroom($id, $class);

            $this->setFlashMessage('success', '基本信息设置成功！');
        }

        $tags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'classroom',
            'ownerId'   => $id
        ));

        return $this->render("classroom-manage/set-info.html.twig", array(
            'classroom' => $classroom,
            'tags'      => ArrayToolkit::column($tags, 'name')
        ));
    }

    public function setPriceAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($this->setting('vip.enabled')) {
            $levels = $this->getLevelService()->findEnabledLevels();
        } else {
            $levels = array();
        }

        if ($request->getMethod() == "POST") {
            $class = $request->request->all();

            if (!isset($class['vipLevelId']) || $class['vipLevelId'] == "") {
                $class['vipLevelId'] = 0;
            }

            $this->setFlashMessage('success', '设置成功！');

            $classroom = $this->getClassroomService()->updateClassroom($id, $class);
        }

        $coinPrice = 0;
        $price     = 0;
        $courses   = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $cashRate = $this->getCashRate();

        foreach ($courses as $course) {
            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        $courseNum = count($courses);

        return $this->render("classroom-manage/set-price.html.twig", array(
            'levels'    => $this->makeLevelChoices($levels),
            'price'     => $price,
            'coinPrice' => $coinPrice,
            'courseNum' => $courseNum,
            'classroom' => $classroom
        ));
    }

    public function setPictureAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render("classroom-manage/set-picture.html.twig", array(
            'classroom' => $classroom
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getClassroomService()->changePicture($classroom['id'], $options["images"]);

            return $this->redirect($this->generateUrl('classroom_manage_set_picture', array('id' => $classroom['id'])));
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 525, 350);

        return $this->render('classroom-manage/picture-crop.html.twig', array(
            'classroom'   => $classroom,
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
        ));
    }

    public function coursesAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $userIds   = array();
        $coinPrice = 0;
        $price     = 0;

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($request->getMethod() == 'POST') {
            $courseIds = $request->request->get('courseIds');

            if (empty($courseIds)) {
                $courseIds = array();
            }

            $this->getClassroomService()->updateClassroomCourses($id, $courseIds);

            $this->setFlashMessage('success', '课程修改成功');

            return $this->redirect($this->generateUrl('classroom_manage_courses', array(
                'id' => $id
            )));
        }

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);

        $cashRate = $this->getCashRate();

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("classroom-manage/courses.html.twig", array(
            'classroom' => $classroom,
            'courses'   => $courses,
            'price'     => $price,
            'coinPrice' => $coinPrice,
            'users'     => $users));
    }

    public function coursesSelectAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $data = $request->request->all();

        if (isset($data['ids']) && $data['ids'] != "") {
            $ids = $data['ids'];
            $ids = explode(",", $ids);
        } else {
            return new Response('success');
        }

        $this->getClassroomService()->addCoursesToClassroom($id, $ids);
        $this->setFlashMessage('success', '课程添加成功');

        return new Response('success');
    }

    public function publishAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        // $classroom = $this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->publishClassroom($id);

        return new Response("success");
    }

    public function checkNameAction(Request $request)
    {
        $nickName = $request->request->get('name');
        $user     = array();

        if ($nickName != "") {
            $user = $this->getUserService()->searchUsers(array('nickname' => $nickName, 'roles' => 'ROLE_TEACHER'), array('createdTime' => 'DESC'), 0, 1);
        }

        $user = $user ? $user[0] : array();

        return $this->render('classroom-manage/teacher-info.html.twig', array(
            'user' => $user));
    }

    public function closeAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        // $classroom = $this->getClassroomService()->getClassroom($id);

        $this->getClassroomService()->closeClassroom($id);

        return new Response("success");
    }

    public function importUsersAction($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->render('classroom-manage/import.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function excelDataImportAction(Request $request, $id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        if ($classroom['status'] != 'published') {
            throw $this->createNotFoundException('未发布班级不能导入学员!');
        }

        return $this->forward('TopxiaWebBundle:Importer:importExcelData', array(
            'request'    => $request,
            'targetId'   => $id,
            'targetType' => 'classroom'
        ));
    }

    public function testpaperAction(Request $request, $id, $status)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
//        $user      = $this->getCurrentUser();
        $classroom = $this->getClassroomService()->getClassroom($id);
        // $member = $this->getClassroomService()->getClassroomMember($id, $user['id']);
        $courses = $this->getClassroomService()->findCoursesByClassroomId($id);

        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->searchTestpapers(array('courseIds' => $courseIds), array(), 0, PHP_INT_MAX);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperCount(array('ids' => $testpaperIds, 'status' => $status)),
            20
        );

        $paperResults = $this->getTestpaperService()->searchTestpapers(
            array('ids' => $testpaperIds, 'status' => $status),
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        $targets   = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function ($target) {
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $teacherIds = ArrayToolkit::column($paperResults, 'checkTeacherId');
        $teachers   = $this->getUserService()->findUsersByIds($teacherIds);

        return $this->render('classroom-manage/testpaper/index.html.twig', array(
            'classroom'    => $classroom,
            'status'       => $status,

            'users'        => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses'      => ArrayToolkit::index($courses, 'id'),
            'testpapers'   => ArrayToolkit::index($testpapers, 'id'),
            'teachers'     => $teachers,
            'paginator'    => $paginator,
            'source'       => 'classroom',
            'targetId'     => $classroom['id']
        ));
    }

    public function homeworkAction($id, $status)
    {
        $this->getClassroomService()->tryHandleClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $currentUser = $this->getCurrentUser();

        if (empty($currentUser)) {
            throw $this->getBiz()->createAccessDeniedException('用户不存在或者尚未登录，请先登录');
        }

        $courses                = $this->getClassroomService()->findCoursesByClassroomId($id);
        $courseIds              = ArrayToolkit::column($courses, 'id');
        $homeworksResultsCounts = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, $status);
        $paginator              = new Paginator(
            $this->get('request'),
            $homeworksResultsCounts,
            10
        );

        $orderBy = array();
        if ($status == 'reviewing') {
            $orderBy = array('usedTime' => 'DESC');
        }

        if ($status == 'finished') {
            $orderBy = array('checkedTime' => 'DESC');
        }

        $homeworksResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus(
            $courseIds, $status, $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $reviewingCount = 0;
        $finishedCount  = 0;
        if ($status == 'reviewing') {
            $reviewingCount = $homeworksResultsCounts;
            $finishedCount  = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'finished');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'reviewing');
            $finishedCount  = $homeworksResultsCounts;
        }

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($homeworksResults, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeworksResults, 'lessonId'));

        $usersIds = ArrayToolkit::column($homeworksResults, 'userId');
        $users    = $this->getUserService()->findUsersByIds($usersIds);
        return $this->render('classroom-manage/homework/index.html.twig', array(
            'classroom'        => $classroom,
            'status'           => $status,
            'users'            => $users,
            'homeworksResults' => $homeworksResults,
            'paginator'        => $paginator,
            'courses'          => $courses,
            'lessons'          => $lessons,
            'reviewingCount'   => $reviewingCount,
            'finishedCount'    => $finishedCount,
            'source'           => 'classroom',
            'targetId'         => $classroom['id']
        ));
    }

    private function getTagIdsFromRequest($request)
    {
        $tags = $request->request->get('tags');
        $tags = explode(',', $tags);
        $tags = $this->getTagService()->findTagsByNames($tags);
        return ArrayToolkit::column($tags, 'id');
    }

    private function calculateUserLearnProgress($classroom, $member)
    {
        $courses            = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds          = ArrayToolkit::column($courses, 'id');
        $findLearnedCourses = array();

        foreach ($courseIds as $key => $value) {
            $learnedCourses = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value, $member['userId']);

            if (!empty($learnedCourses)) {
                $findLearnedCourses[] = $learnedCourses;
            }
        }

        $learnedCoursesCount = count($findLearnedCourses);
        $coursesCount        = count($courses);

        if ($coursesCount == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($learnedCoursesCount / $coursesCount * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $learnedCoursesCount,
            'total'   => $coursesCount
        );
    }

    private function makeLevelChoices($levels)
    {
        $choices = array();

        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }

        return $choices;
    }

    private function getUserIds($keyword)
    {
        $userIds = array();

        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserService()->getUserByEmail($keyword);

            $userIds[] = $user ? $user['id'] : null;
            return $userIds;
        } elseif (SimpleValidator::mobile($keyword)) {
            $mobileVerifiedUser = $this->getUserService()->getUserByVerifiedMobile($keyword);
            $profileUsers       = $this->getUserService()->searchUserProfiles(array('tel' => $keyword), array('id' => 'DESC'), 0, PHP_INT_MAX);
            $mobileNameUser     = $this->getUserService()->getUserByNickname($keyword);
            $userIds            = $profileUsers ? ArrayToolkit::column($profileUsers, 'id') : null;

            $userIds[] = $mobileVerifiedUser ? $mobileVerifiedUser['id'] : null;
            $userIds[] = $mobileNameUser ? $mobileNameUser['id'] : null;

            $userIds = array_unique($userIds);

            $userIds = $userIds ? $userIds : null;
            return $userIds;
        } else {
            $user      = $this->getUserService()->getUserByNickname($keyword);
            $userIds[] = $user ? $user['id'] : null;
            return $userIds;
        }
    }

    protected function getCashRate()
    {
        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
        $cashRate    = $coinEnable && isset($coinSetting['cash_rate']) ? $coinSetting["cash_rate"] : 1;
        return $cashRate;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ClassroomReviewService
     */
    protected function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->createService('Vip:Vip.LevelService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return WebExtension
     */
    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return HomeworkService
     */
    protected function getHomeworkService()
    {
        return $this->createService('Homework:Homework.HomeworkService');
    }
}
