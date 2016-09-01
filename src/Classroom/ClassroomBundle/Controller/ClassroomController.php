<?php

namespace Classroom\ClassroomBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ExtensionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;

class ClassroomController extends BaseController
{
    public function dashboardAction($nav, $classroom, $member)
    {
        $canManageClassroom = $this->getClassroomService()->canManageClassroom($classroom["id"]);

        return $this->render("ClassroomBundle:Classroom:dashboard-nav.html.twig", array(
            'canManageClassroom' => $canManageClassroom,
            'classroom'          => $classroom,
            'nav'                => $nav,
            'member'             => $member
        ));
    }

    public function exploreAction(Request $request, $category)
    {
        $conditions             = $request->query->all();
        $conditions['status']   = 'published';
        $conditions['showable'] = 1;

        $categoryArray = array();

        if (!empty($category)) {
            $categoryArray             = $this->getCategoryService()->getCategoryByCode($category);
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
            $categoryIds               = array_merge($childrenIds, array($categoryArray['id']));
            $conditions['categoryIds'] = $categoryIds;
        }

        if (!isset($conditions['filter'])) {
            $conditions['filter'] = array(
                'price'          => 'all',
                'currentLevelId' => 'all'
            );
        }

        $filter = $conditions['filter'];

        if ($filter['price'] == 'free') {
            $conditions['price'] = '0.00';
        }

        unset($conditions['filter']);
        $levels = array();

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');

            if (!$filter['currentLevelId'] != 'all') {
                $vipLevelIds               = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($filter['currentLevelId']), 'id');
                $conditions['vipLevelIds'] = array_merge(array($filter['currentLevelId']), $vipLevelIds);
            }
        }

        $orderBy = !isset($conditions['orderBy']) ? 'createdTime' : $conditions['orderBy'];
        unset($conditions['orderBy']);

        $conditions['recommended'] = ($orderBy == 'recommendedSeq') ? 1 : null;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            9
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array($orderBy, 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace("/ /", "", $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $categoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $categoryParent = '';
            } else {
                $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return $this->render("ClassroomBundle:Classroom:explore.html.twig", array(
            'paginator'                => $paginator,
            'classrooms'               => $classrooms,
            'path'                     => 'classroom_explore',
            'category'                 => $category,
            'categoryArray'            => $categoryArray,
            'categoryArrayDescription' => $categoryArrayDescription,
            'categoryParent'           => $categoryParent,
            'filter'                   => $filter,
            'levels'                   => $levels,
            'orderBy'                  => $orderBy
        ));
    }

    public function keywordsAction($classroom)
    {
        $category       = $this->getCategoryService()->getCategory($classroom['categoryId']);
        $parentCategory = array();

        if (!empty($category) && $category['parentId'] != 0) {
            $parentCategory = $this->getCategoryService()->getCategory($category['parentId']);
        }

        return $this->render('ClassroomBundle:Classroom:keywords.html.twig', array(
            'category'       => $category,
            'parentCategory' => $parentCategory,
            'classroom'      => $classroom
        ));
    }

    public function myClassroomAction()
    {
        $user       = $this->getCurrentUser();
        $progresses = array();
        $classrooms = array();

        $studentClassrooms = $this->getClassroomService()->searchMembers(array(
            'role'   => 'student',
            'userId' => $user->id
        ), array('createdTime', 'desc'), 0, PHP_INT_MAX);

        $auditorClassrooms = $this->getClassroomService()->searchMembers(array(
            'role'   => 'auditor',
            'userId' => $user->id
        ), array('createdTime', 'desc'), 0, PHP_INT_MAX);

        $classrooms = array_merge($studentClassrooms, $auditorClassrooms);

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses      = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $classroomId = array($classroom['id']);
            $member      = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomId);
            $time        = time() - $member[$classroom['id']]['createdTime'];
            $day         = intval($time / (3600 * 24));

            $classrooms[$key]['day'] = $day;

            $progresses[$classroom['id']] = $this->calculateUserLearnProgress($classroom, $user->id);
        }

        $members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);
        return $this->render("ClassroomBundle:Classroom:my-classroom.html.twig", array(
            'classrooms' => $classrooms,
            'members'    => $members,
            'progresses' => $progresses
        ));
    }

    public function headerAction($previewAs, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        $coursesNum = count($courses);

        $checkMemberLevelResult = $classroomMemberLevel = null;

        if ($this->setting('vip.enabled')) {
            $classroomMemberLevel = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;

            if ($classroomMemberLevel) {
                $checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroomMemberLevel['id']);
            }
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($previewAs) {
            if (!$this->getClassroomService()->canManageClassroom($classroomId)) {
                $previewAs = "";
            }
        }

        $member    = $this->previewAsMember($previewAs, $member, $classroom);
        $lessonNum = 0;
        $coinPrice = 0;
        $price     = 0;

        $cashRate = $this->getCashRate();

        foreach ($courses as $key => $course) {
            $lessonNum += $course['lessonNum'];

            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        $canFreeJoin = $this->canFreeJoin($classroom, $courses, $user, $classroom);
        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($classroom['categoryId']);

        if (!empty($member['role'])) {
            $isclassroomteacher = in_array('teacher', $member['role']) || in_array('headTeacher', $member['role']) ? true : false;
        } else {
            $isclassroomteacher = false;
        }

        if ($member) {
            return $this->render("ClassroomBundle:Classroom:classroom-join-header.html.twig", array(
                'classroom'              => $classroom,
                'courses'                => $courses,
                'lessonNum'              => $lessonNum,
                'coinPrice'              => $coinPrice,
                'price'                  => $price,
                'member'                 => $member,
                'checkMemberLevelResult' => $checkMemberLevelResult,
                'classroomMemberLevel'   => $classroomMemberLevel,
                'coursesNum'             => $coursesNum,
                'canFreeJoin'            => $canFreeJoin,
                'breadcrumbs'            => $breadcrumbs,
                'isclassroomteacher'     => $isclassroomteacher
            ));
        }

        return $this->render("ClassroomBundle:Classroom:classroom-header.html.twig", array(
            'classroom'              => $classroom,
            'courses'                => $courses,
            'checkMemberLevelResult' => $checkMemberLevelResult,
            'classroomMemberLevel'   => $classroomMemberLevel,
            'coursesNum'             => $coursesNum,
            'member'                 => $member,
            'canFreeJoin'            => $canFreeJoin,
            'breadcrumbs'            => $breadcrumbs
        ));
    }

    /**
     * 如果用户已购买了此班级，或者用户是该班级的教师，则显示班级的Dashboard界面。
     * 如果用户未购买该班级，那么显示课程的营销界面。
     */
    public function showAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        $previewAs = "";

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $currentUser = $this->getUserService()->getCurrentUser();

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($request->query->get('previewAs')) {
            if ($this->getClassroomService()->canManageClassroom($id)) {
                $previewAs = $request->query->get('previewAs');
            }
        }

        if ($this->isPluginInstalled('ClassroomPlan')) {
            $plan = $this->getClassroomPlanService()->getPlanByClassroomId($id);

            if ($plan && $plan['status'] == 'published') {
                return $this->redirect($this->generateUrl('classroom_plan_tab', array(
                    'classroomId' => $id
                )));
            }
        }

        $member = $this->previewAsMember($previewAs, $member, $classroom);

        if ($member && $member["locked"] == "0") {
            if (in_array('student', $member['role'])) {
                return $this->redirect($this->generateUrl('classroom_courses', array(
                    'classroomId' => $id
                )));
            } else {
                return $this->redirect($this->generateUrl('classroom_threads', array(
                    'classroomId' => $id
                )));
            }
        }

        return $this->redirect($this->generateUrl('classroom_introductions', array(
            'id' => $id
        )));
    }

    private function previewAsMember($previewAs, $member, $classroom)
    {
        $user = $this->getCurrentUser();

        if (in_array($previewAs, array('guest', 'auditor', 'member'))) {
            if ($previewAs == 'guest') {
                return;
            }

            $member = array(
                'id'          => 0,
                'classroomId' => $classroom['id'],
                'userId'      => $user['id'],
                'orderId'     => 0,
                'levelId'     => 0,
                'noteNum'     => 0,
                'threadNum'   => 0,
                'remark'      => '',
                'role'        => array('auditor'),
                'locked'      => 0,
                'createdTime' => 0
            );

            if ($previewAs == 'member') {
                $member['role'] = array('member');
            }
        }

        return $member;
    }

    public function introductionAction(Request $request, $id)
    {
        $classroom    = $this->getClassroomService()->getClassroom($id);
        $introduction = $classroom['about'];
        $user         = $this->getCurrentUser();
        $member       = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroom['title']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace("/ /", "", $classroomDescription);
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';

        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render("ClassroomBundle:Classroom:introduction.html.twig", array(
            'introduction'         => $introduction,
            'layout'               => $layout,
            'classroom'            => $classroom,
            'member'               => $member,
            'classroomDescription' => $classroomDescription
        ));
    }

    public function teachersBlockAction($classroom)
    {
        $classroomTeacherIds = $this->getClassroomService()->findTeachers($classroom['id']);
        $users               = $this->getUserService()->findUsersByIds($classroomTeacherIds);
        $headTeacher         = $this->getUserService()->getUser($classroom['headTeacherId']);
        $headTeacherprofiles = $this->getUserService()->getUserProfile($classroom['headTeacherId']);
        $profiles            = $this->getUserService()->findUserProfilesByIds($classroomTeacherIds);
        $currentUser         = $this->getCurrentUser();

        $isFollowed = false;

        if ($headTeacher && $currentUser->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($currentUser['id'], $headTeacher['id']);
        }

        if ($headTeacher && !(in_array($headTeacher, $users))) {
            $teachersCount = 1 + count($users);
        } else {
            $teachersCount = count($users);
        }

        return $this->render('ClassroomBundle:Classroom:teachers-block.html.twig', array(
            'classroom'           => $classroom,
            'users'               => $users,
            'profiles'            => $profiles,
            'headTeacher'         => $headTeacher,
            'headTeacherprofiles' => $headTeacherprofiles,
            'teachersCount'       => $teachersCount,
            'isFollowed'          => $isFollowed
        ));
    }

    public function roleAction($previewAs, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $user = $this->getCurrentUser();

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        $checkMemberLevelResult = $classroomMemberLevel = null;

        if ($this->setting('vip.enabled')) {
            $classroomMemberLevel = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;

            if ($classroomMemberLevel) {
                $checkMemberLevelResult = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroomMemberLevel['id']);
            }
        }

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($previewAs) {
            if (!$this->getClassroomService()->canManageClassroom($classroomId)) {
                $previewAs = "";
            }
        }

        $member = $this->previewAsMember($previewAs, $member, $classroom);

        $coinPrice = 0;
        $price     = 0;

        $cashRate = $this->getCashRate();

        foreach ($courses as $key => $course) {
            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        if ($member && $member["locked"] == "0") {
            return $this->render("ClassroomBundle:Classroom:role.html.twig", array(
                'classroom'              => $classroom,
                'courses'                => $courses,
                'coinPrice'              => $coinPrice,
                'price'                  => $price,
                'member'                 => $member,
                'checkMemberLevelResult' => $checkMemberLevelResult,
                'classroomMemberLevel'   => $classroomMemberLevel
            ));
        }

        return new Response();
    }

    public function latestMembersBlockAction($classroom, $count = 10)
    {
        $students = $this->getClassroomService()->findClassroomStudents($classroom['id'], 0, 20);
        $users    = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));

        return $this->render('ClassroomBundle:Classroom:latest-members-block.html.twig', array(
            'students' => $students,
            'users'    => $users
        ));
    }

    public function classroomStatusBlockAction($classroom, $count = 10)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);

        $learns = array();

        if ($courses) {
            $courseIds                        = ArrayToolkit::column($courses, 'id');
            $conditions['classroomCourseIds'] = $courseIds;
            $conditions['classroomId']        = $classroom['id'];
        } else {
            $conditions['onlyClassroomId'] = $classroom['id'];
        }

        $learns = $this->getStatusService()->searchStatuses(
            $conditions,
            array('createdTime', 'DESC'),
            0,
            $count
        );

        if ($learns) {
            $ownerIds = ArrayToolkit::column($learns, 'userId');
            $owners   = $this->getUserService()->findUsersByIds($ownerIds);

            $manager = ExtensionManager::instance();

            foreach ($learns as $key => $learn) {
                $learns[$key]['user']    = $owners[$learn['userId']];
                $learns[$key]['message'] = $manager->renderStatus($learn, 'simple');
                unset($learn);
            }
        }

        return $this->render('TopxiaWebBundle:Status:status-block.html.twig', array(
            'learns' => $learns
        ));
    }

    public function signPageAction($classroomId)
    {
        $user = $this->getCurrentUser();

        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $isSignedToday = $this->getSignService()->isSignedToday($user->id, 'classroom_sign', $classroom['id']);

        $week = array('日', '一', '二', '三', '四', '五', '六');

        $userSignStatistics = $this->getSignService()->getSignUserStatistics($user->id, 'classroom_sign', $classroom['id']);

        $day = date('d', time());

        $signDay = $this->getSignService()->getSignRecordsByPeriod($user->id, 'classroom_sign', $classroom['id'], date('Y-m', time()), date('Y-m-d', time() + 3600));
        $notSign = $day - count($signDay);

        return $this->render("ClassroomBundle:Classroom:sign.html.twig", array(
            'classroom'          => $classroom,
            'isSignedToday'      => $isSignedToday,
            'userSignStatistics' => $userSignStatistics,
            'notSign'            => $notSign,
            'week'               => $week[date('w', time())]));
    }

    public function signAction(Request $request, $classroomId)
    {
        $user               = $this->getCurrentUser();
        $userSignStatistics = array();

        $this->checkClassroomStatus($classroomId);

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);

        if ($this->getClassroomService()->canTakeClassroom($classroomId) || (isset($member) && array_intersect(array('auditor'), $member['role']))) {
            $this->getSignService()->userSign($user['id'], 'classroom_sign', $classroomId);

            $userSignStatistics = $this->getSignService()->getSignUserStatistics($user->id, 'classroom_sign', $classroomId);
        }

        return $this->createJsonResponse($userSignStatistics);
    }

    public function getSignedRecordsByPeriodAction(Request $request, $classroomId)
    {
        $user   = $this->getCurrentUser();
        $userId = $user['id'];

        $startDay = $request->query->get('startDay');
        $endDay   = $request->query->get('endDay');

        $userSigns         = $this->getSignService()->getSignRecordsByPeriod($userId, 'classroom_sign', $classroomId, $startDay, $endDay);
        $result            = array();
        $result['records'] = array();

        if ($userSigns) {
            foreach ($userSigns as $userSign) {
                $result['records'][] = array(
                    'day'  => date('d', $userSign['createdTime']),
                    'time' => date('G点m分', $userSign['createdTime']),
                    'rank' => $userSign['rank']);
            }
        }

        $userSignStatistics  = $this->getSignService()->getSignUserStatistics($userId, 'classroom_sign', $classroomId);
        $classSignStatistics = $this->getSignService()->getSignTargetStatistics('classroom_sign', $classroomId, date('Ymd', time()));

        $result['todayRank'] = $this->getSignService()->getTodayRank($userId, 'classroom_sign', $classroomId);
        $result['signedNum'] = $classSignStatistics['signedNum'];
        $result['keepDays']  = $userSignStatistics['keepDays'];

        return $this->createJsonResponse($result);
    }

    public function becomeStudentAction(Request $request, $id)
    {
        if (!$this->setting('vip.enabled')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $this->getClassroomService()->becomeStudent($id, $user['id'], array('becomeUseMember' => true));

        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function exitAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        $member = $this->getClassroomService()->getClassroomMember($id, $user["id"]);

        if (empty($member)) {
            throw $this->createAccessDeniedException('您不是班级的学员。');
        }

        if (!$this->getClassroomService()->canTakeClassroom($id, true)) {
            throw $this->createAccessDeniedException('您不是班级的学员。');
        }

        if (!empty($member['orderId'])) {
            throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);

        if ($order['targetType'] == 'groupSell') {
            throw $this->createAccessDeniedException('组合购买课程不能退出。');
        }

        $this->getClassroomService()->exitClassroom($id, $user["id"]);

        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function becomeAuditorAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $classroom = $this->getClassroomService()->getClassroom($id);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        if (!$classroom['buyable']) {
            return $this->createMessageResponse('info', "非常抱歉，该{$classroom['title']}不允许加入，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        if ($this->getClassroomService()->canTakeClassroom($id)) {
            $member = $this->getClassroomService()->getClassroomMember($id, $user['id']);

            if ($member) {
                goto response;
            }
        }

        if ($this->getClassroomService()->isClassroomAuditor($id, $user["id"])) {
            goto response;
        }

        $this->getClassroomService()->becomeAuditor($id, $user["id"]);

        response:
        return $this->redirect($this->generateUrl('classroom_show', array('id' => $id)));
    }

    public function canviewAction(Request $request, $classroomId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->getClassroomService()->canLookClassroom($classroomId);

        return $this->createJsonResponse($result);
    }

    public function classroomBlockAction($courseId)
    {
        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);

        $classroom = empty($classroomIds) || count($classroomIds) == 0 ? null : $this->getClassroomService()->getClassroom($classroomIds[0]);

        return $this->render("ClassroomBundle:Classroom:classroom-block.html.twig", array(
            'classroom' => $classroom
        ));
    }

    public function buyAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $previewAs = $request->query->get('previewAs');

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        $member = $this->previewAsMember($previewAs, $member, $classroom);

        $courseSetting = $this->getSettingService()->get('course', array());

        $userInfo                   = $this->getUserService()->getUserProfile($user['id']);
        $userInfo['approvalStatus'] = $user['approvalStatus'];

        $account = $this->getCashAccountService()->getAccountByUserId($user['id'], true);

        if (empty($account)) {
            $this->getCashAccountService()->createAccount($user['id']);
        }

        if (isset($account['cash'])) {
            $account['cash'] = intval($account['cash']);
        }

        $amount = $this->getOrderService()->analysisAmount(array('userId' => $user->id, 'status' => 'paid'));
        $amount += $this->getCashOrdersService()->analysisAmount(array('userId' => $user->id, 'status' => 'paid'));

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        //判断用户是否为VIP
        $vipStatus = $classroomVip = null;

        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $classroomVip = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;

            if ($classroomVip) {
                $vipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroomVip['id']);
            }
        }

        return $this->render('ClassroomBundle:Classroom:buy-modal.html.twig', array(
            'classroom'        => $classroom,
            'payments'         => $this->getEnabledPayments(),
            'user'             => $userInfo,
            'noVerifiedMobile' => (strlen($user['verifiedMobile']) == 0),
            'verifiedMobile'   => (strlen($user['verifiedMobile']) > 0) ? $user['verifiedMobile'] : '',
            'courseSetting'    => $courseSetting,
            'member'           => $member,
            'userFields'       => $userFields,
            'account'          => $account,
            'amount'           => $amount,
            'vipStatus'        => $vipStatus
        ));
    }

    public function modifyUserInfoAction(Request $request)
    {
        $formData = $request->request->all();

        $user = $this->getCurrentUser();

        if (empty($user)) {
            return $this->createMessageResponse('error', '用户未登录，不能购买。');
        }

        $classroom = $this->getClassroomService()->getClassroom($formData['targetId']);

        if (empty($classroom)) {
            return $this->createMessageResponse('error', "{$classroom['title']}不存在，不能购买。");
        }

        $userInfo = ArrayToolkit::parts($formData, array(
            'truename',
            'mobile',
            'qq',
            'company',
            'weixin',
            'weibo',
            'idcard',
            'gender',
            'job',
            'intField1', 'intField2', 'intField3', 'intField4', 'intField5',
            'floatField1', 'floatField2', 'floatField3', 'floatField4', 'floatField5',
            'dateField1', 'dateField2', 'dateField3', 'dateField4', 'dateField5',
            'varcharField1', 'varcharField2', 'varcharField3', 'varcharField4', 'varcharField5', 'varcharField10', 'varcharField6', 'varcharField7', 'varcharField8', 'varcharField9',
            'textField1', 'textField2', 'textField3', 'textField4', 'textField5', 'textField6', 'textField7', 'textField8', 'textField9', 'textField10'
        ));

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
            $this->authenticateUser($this->getUserService()->getUser($user['id']));

            if (!$user['setup']) {
                $this->getUserService()->setupAccount($user['id']);
            }
        }

        $coinSetting = $this->setting("coin");

        //判断用户是否为VIP
        $vipStatus = $classroomVip = null;

        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $classroomVip = $classroom['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($classroom['vipLevelId']) : null;

            if ($classroomVip) {
                $vipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']);

                if ($vipStatus == 'ok') {
                    $formData['becomeUseMember'] = true;
                }
            }
        }

        if ($classroom['price'] == 0 || $vipStatus == 'ok') {
            $formData['amount']     = 0;
            $formData['totalPrice'] = 0;
            $formData['priceType']  = empty($coinSetting["priceType"]) ? 'RMB' : $coinSetting["priceType"];
            $formData['coinRate']   = empty($coinSetting["coinRate"]) ? 1 : $coinSetting["coinRate"];
            $formData['coinAmount'] = 0;
            $formData['vipStatus']  = 'ok';

            $order = $this->getClassroomOrderService()->createOrder($formData);

            if ($order['status'] == 'paid') {
                return $this->redirect($this->generateUrl('classroom_show', array('id' => $order['targetId'])));
            }
        }

        return $this->redirect($this->generateUrl('order_show', array(
            'targetId'   => $formData['targetId'],
            'targetType' => 'classroom'
        )));
    }

    public function qrcodeAction(Request $request, $id)
    {
        $user = $this->getUserService()->getCurrentUser();
        $host = $request->getSchemeAndHttpHost();

        $token = $this->getTokenService()->makeToken('qrcode', array(
            'userId'   => $user['id'],
            'data'     => array(
                'url'    => $this->generateUrl('classroom_show', array('id' => $id), true),
                'appUrl' => "{$host}/mapi_v2/mobile/main#/classroom/{$id}"
            ),
            'times'    => 1,
            'duration' => 3600
        ));
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        $response = array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true)
        );
        return $this->createJsonResponse($response);
    }

    private function canFreeJoin($classroom, $courses, $user)
    {
        $classroomSetting = $this->getSettingService()->get('classroom');

        if (empty($classroomSetting['discount_buy'])) {
            return false;
        }

        $courseIds         = ArrayToolkit::column($courses, "parentId");
        $courses           = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseMembers     = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($user["id"], $courseIds);
        $isJoinedCourseIds = ArrayToolkit::column($courseMembers, "courseId");
        $courses           = $this->getCourseService()->findCoursesByIds($isJoinedCourseIds);
        $priceType         = "RMB";

        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;

        if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
            $priceType = $coinSetting["price_type"];
        }

        $totalPrice = $classroom["price"];

        if ($priceType == "Coin") {
            $totalPrice = $totalPrice * $coinSetting["cash_rate"];
        }

        $classroomSetting = $this->getSettingService()->get("classroom");

        if ($this->getCoursesTotalPrice($courses, $priceType) >= (float) $totalPrice) {
            return true;
        }

        return false;
    }

    private function getCoursesTotalPrice($courses, $priceType)
    {
        $coursesTotalPrice = 0;

        $cashRate = $this->getCashRate();

        foreach ($courses as $key => $course) {
            if ($priceType == "RMB") {
                $coursesTotalPrice += $course["originPrice"];
            } elseif ($priceType == "Coin") {
                $coursesTotalPrice += $course["originPrice"] * $cashRate;
            }
        }

        return $coursesTotalPrice;
    }

    private function checkClassroomStatus($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw $this->createNotFoundException();
        }

        if ($classroom['status'] != "published") {
            throw $this->createNotFoundException();
        }
    }

    private function calculateUserLearnProgress($classroom, $userId)
    {
        $courses            = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds          = ArrayToolkit::column($courses, 'id');
        $findLearnedCourses = array();

        foreach ($courseIds as $key => $value) {
            $learnedCourses = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value, $userId);

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

    public function classroomThreadsAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classrooms            = array();
        $teacherClassrooms     = $this->getClassroomService()->searchMembers(array('role' => 'teacher', 'userId' => $user->id), array('createdTime', 'desc'), 0, PHP_INT_MAX);
        $headTeacherClassrooms = $this->getClassroomService()->searchMembers(array('role' => 'headTeacher', 'userId' => $user->id), array('createdTime', 'desc'), 0, PHP_INT_MAX);

        $classrooms = array_merge($teacherClassrooms, $headTeacherClassrooms);

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        if (empty($classrooms)) {
            return $this->render('ClassroomBundle:Classroom:my-teaching-threads.html.twig', array(
                'type'       => $type,
                'threadType' => 'classroom',
                'threads'    => array()
            ));
        }

        $conditions = array(
            'targetIds'  => $classroomIds,
            'targetType' => 'classroom',
            'type'       => $type
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));

        return $this->render('ClassroomBundle:Classroom:my-teaching-threads.html.twig', array(
            'paginator'  => $paginator,
            'threads'    => $threads,
            'users'      => $users,
            'classrooms' => $classrooms,
            'type'       => $type,
            'threadType' => 'classroom'
        ));
    }

    public function classroomDiscussionsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId'     => $user['id'],
            'type'       => 'discussion',
            'targetType' => 'classroom'
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users      = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'lastPostUserId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('ClassroomBundle:Classroom:classroom-discussions.html.twig', array(
            'threadType' => 'classroom',
            'paginator'  => $paginator,
            'threads'    => $threads,
            'users'      => $users,
            'classrooms' => $classrooms
        ));
    }

    protected function getCashRate()
    {
        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
        $cashRate    = $coinEnable && isset($coinSetting['cash_rate']) ? $coinSetting["cash_rate"] : 1;
        return $cashRate;
    }

    public function orderInfoAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (empty($order)) {
            throw $this->createNotFoundException('订单不存在!');
        }

        $classroom = $this->getClassroomService()->getClassroom($order['targetId']);

        if (empty($classroom)) {
            throw $this->createNotFoundException("找不到要购买的班级!");
        }

        return $this->render('ClassroomBundle:Classroom:classroom-order.html.twig', array('order' => $order, 'classroom' => $classroom));
    }

    protected function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payment  = $this->get('topxia.twig.web_extension')->getDict('payment');
        $payNames = array_keys($payment);
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName.'_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName.'_type']) ? '' : $setting[$payName.'_type']
                );
            }
        }

        return $enableds;
    }

    public function memberIdsAction(Request $request, $id)
    {
        $ids = $this->getClassroomService()->findMemberUserIdsByClassroomId($id);
        return $this->createJsonResponse($ids);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getSignService()
    {
        return $this->getServiceKernel()->createService('Sign.SignService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getClassroomOrderService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomOrderService');
    }

    protected function getClassroomReviewService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomReviewService');
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getCashAccountService()
    {
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getClassroomPlanService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}
