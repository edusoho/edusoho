<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends CourseBaseController
{
    public function exploreAction(Request $request, $category)
    {
        $conditions    = $request->query->all();
        $categoryArray = array();
        $levels        = array();

        $conditions['code'] = $category;

        if (!empty($conditions['code'])) {
            $categoryArray             = $this->getCategoryService()->getCategoryByCode($conditions['code']);
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
            $categoryIds               = array_merge($childrenIds, array($categoryArray['id']));
            $conditions['categoryIds'] = $categoryIds;
        }

        unset($conditions['code']);

        if (!isset($conditions['filter'])) {
            $conditions['filter'] = array(
                'type'           => 'all',
                'price'          => 'all',
                'currentLevelId' => 'all'
            );
        }

        $filter = $conditions['filter'];

        if ($filter['price'] == 'free') {
            $coinSetting = $this->getSettingService()->get("coin");
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $priceType   = "RMB";

            if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
                $priceType = $coinSetting["price_type"];
            }

            if ($priceType == 'RMB') {
                $conditions['price'] = '0.00';
            } else {
                $conditions['price'] = '0.00';
            }
        }

        if ($filter['type'] == 'live') {
            $conditions['type'] = 'live';
        }

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');

            if ($filter['currentLevelId'] != 'all') {
                $vipLevelIds               = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($filter['currentLevelId']), 'id');
                $conditions['vipLevelIds'] = array_merge(array($filter['currentLevelId']), $vipLevelIds);
            }
        }

        unset($conditions['filter']);

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['explore_default_orderBy'])) {
            $courseSetting['explore_default_orderBy'] = 'latest';
        }

        $orderBy = $courseSetting['explore_default_orderBy'];
        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        $conditions['parentId'] = 0;
        $conditions['status']   = 'published';
        $paginator              = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            20
        );

        if ($orderBy != 'recommendedSeq') {
            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        if ($orderBy == 'recommendedSeq') {
            $conditions['recommended'] = 1;
            $recommendCount            = $this->getCourseService()->searchCourseCount($conditions);
            $currentPage               = $request->query->get('page') ? $request->query->get('page') : 1;
            $recommendPage             = intval($recommendCount / 20);
            $recommendLeft             = $recommendCount % 20;

            if ($currentPage <= $recommendPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
            } elseif (($recommendPage + 1) == $currentPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
                $conditions['recommended'] = 0;
                $coursesTemp               = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    0,
                    20 - $recommendLeft
                );
                $courses = array_merge($courses, $coursesTemp);
            } else {
                $conditions['recommended'] = 0;
                $courses                   = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    (20 - $recommendLeft) + ($currentPage - $recommendPage - 2) * 20,
                    20
                );
            }
        }

        $group = $this->getCategoryService()->getGroupByCode('course');

        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        }

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

        return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
            'courses'                  => $courses,
            'category'                 => $category,
            'filter'                   => $filter,
            'orderBy'                  => $orderBy,
            'paginator'                => $paginator,
            'categories'               => $categories,
            'consultDisplay'           => true,
            'path'                     => 'course_explore',
            'categoryArray'            => $categoryArray,
            'group'                    => $group,
            'categoryArrayDescription' => $categoryArrayDescription,
            'categoryParent'           => $categoryParent,
            'levels'                   => $levels
        ));
    }

    public function archiveAction(Request $request)
    {
        $conditions = array(
            'status'   => 'published',
            'parentId' => '0'
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions), 30
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();

        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:archive.html.twig', array(
            'courses'   => $courses,
            'paginator' => $paginator,
            'users'     => $users
        ));
    }

    public function archiveCourseAction(Request $request, $id)
    {
        $course   = $this->getCourseService()->getCourse($id);
        $lessons  = $this->getCourseService()->searchLessons(array('courseId' => $course['id'], 'status' => 'published'), array('createdTime', 'ASC'), 0, 1000);
        $tags     = $this->getTagService()->findTagsByIds($course['tags']);
        $category = $this->getCategoryService()->getCategory($course['categoryId']);

        if (!$course) {
            $courseDescription = array();
        } else {
            $courseDescription = $course['about'];
            $courseDescription = strip_tags($courseDescription, '');
            $courseDescription = preg_replace("/ /", "", $courseDescription);
            $courseDescription = substr($courseDescription, 0, 100);
        }

        return $this->render('TopxiaWebBundle:Course:archiveCourse.html.twig', array(
            'course'            => $course,
            'lessons'           => $lessons,
            'tags'              => $tags,
            'category'          => $category,
            'courseDescription' => $courseDescription
        ));
    }

    public function archiveLessonAction(Request $request, $id, $lessonId)
    {
        $course = $this->getCourseService()->getCourse($id);

        $lessons = $this->getCourseService()->searchLessons(array('courseId' => $course['id'], 'status' => 'published'), array('createdTime', 'ASC'), 0, 1000);

        $tags = $this->getTagService()->findTagsByIds($course['tags']);

        if ($lessonId == '' && $lessons != null) {
            $currentLesson = $lessons[0];
        } else {
            $currentLesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        }

        return $this->render('TopxiaWebBundle:Course:old_archiveLesson.html.twig', array(
            'course'        => $course,
            'lessons'       => $lessons,
            'currentLesson' => $currentLesson,
            'tags'          => $tags
        ));
    }

    public function infoAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该班级，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        $category = $this->getCategoryService()->getCategory($course['categoryId']);
        $tags     = $this->getTagService()->findTagsByIds($course['tags']);

        return $this->render('TopxiaWebBundle:Course:info.html.twig', array(
            'course'   => $course,
            'member'   => $member,
            'category' => $category,
            'tags'     => $tags
        ));
    }

    public function LessonListAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该班级，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        return $this->render('TopxiaWebBundle:Course:lesson-list.html.twig', array(
            'course' => $course,
            'member' => $member
        ));
    }

    public function membersAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->getCourseStudentCount($course['id']),
            6
        );

        $students = $this->getCourseService()->findCourseStudents(
            $course['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users          = $this->getUserService()->findUsersByIds($studentUserIds);
        $followingIds   = $this->getUserService()->filterFollowingIds($this->getCurrentUser()->id, $studentUserIds);

        $progresses = array();

        foreach ($students as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
        }

        return $this->render('TopxiaWebBundle:Course:members-modal.html.twig', array(
            'course'       => $course,
            'students'     => $students,
            'users'        => $users,
            'progresses'   => $progresses,
            'followingIds' => $followingIds,
            'paginator'    => $paginator,
            'canManage'    => $this->getCourseService()->canManageCourse($course['id'])
        ));
    }

    public function showAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该班级，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        if (empty($member)) {
            $user   = $this->getCurrentUser();
            $member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user->id);

            if (isset($member["id"])) {
                $course['studentNum']++;
            }
        }

        $this->getCourseService()->hitCourse($id);

        $items = $this->getCourseService()->getCourseItems($course['id']);

        return $this->render("TopxiaWebBundle:Course:{$course['type']}-show.html.twig", array(
            'course' => $course,
            'member' => $member,
            'items'  => $items
        ));
    }

    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $member['learnedNum'],
            'total'   => $course['lessonNum']
        );
    }

    public function favoriteAction(Request $request, $id)
    {
        $this->getCourseService()->favoriteCourse($id);

        return $this->createJsonResponse(true);
    }

    public function unfavoriteAction(Request $request, $id)
    {
        $this->getCourseService()->unfavoriteCourse($id);

        return $this->createJsonResponse(true);
    }

    public function createAction(Request $request)
    {
        $user        = $this->getUserService()->getCurrentUser();
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        if (false === $this->get('security.context')->isGranted('ROLE_TEACHER')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $course = $request->request->all();

            if ($course['type'] == 'live' || $course['type'] == 'liveOpen') {
                try {
                    $this->_checkLiveCloudSetting($course['type']);
                } catch (\Exception $e) {
                    return $this->createMessageResponse('info', $e->getMessage());
                }
            }

            if ($course['type'] == 'open' || $course['type'] == 'liveOpen') {
                return $this->forward('TopxiaWebBundle:OpenCourse:create', array(
                    'request' => $request
                ));
            }

            $course = $this->getCourseService()->createCourse($course);

            return $this->redirect($this->generateUrl('course_manage', array('id' => $course['id'])));
        }

        return $this->render('TopxiaWebBundle:Course:create.html.twig', array(
            'userProfile' => $userProfile
        ));
    }

    public function exitAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $user                  = $this->getCurrentUser();

        if (empty($member)) {
            throw $this->createAccessDeniedException('您不是课程的学员。');
        }

        if ($member["joinedType"] == "course" && !empty($member['orderId'])) {
            throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
        }

        $this->getCourseService()->removeStudent($course['id'], $user['id']);

        return $this->createJsonResponse(true);
    }

    public function becomeUseMemberAction(Request $request, $id)
    {
        if (!$this->setting('vip.enabled')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $this->getCourseService()->becomeStudent($id, $user['id'], array('becomeUseMember' => true));

        return $this->createJsonResponse(true);
    }

    public function learnAction(Request $request, $id)
    {
        $user      = $this->getCurrentUser();
        $starttime = $request->query->get('starttime', '');

        if (!$user->isLogin()) {
            $request->getSession()->set('_target_path', $this->generateUrl('course_show', array('id' => $id)));

            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        if ($course['approval'] == 1 && ($user['approvalStatus'] != 'approved')) {
            return $this->createMessageResponse('info', "该课程需要通过实名认证，你还没有通过实名认证。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        if (!$this->getCourseService()->canTakeCourse($id)) {
            return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        try {
            list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

            if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
            }

            if ($member && $member['levelId'] > 0) {
                if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                    return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
                }
            }
        } catch (Exception $e) {
            throw $this->createAccessDeniedException('抱歉，未发布课程不能学习！');
        }

        return $this->render('TopxiaWebBundle:Course:learn.html.twig', array(
            'course'    => $course,
            'starttime' => $starttime
        ));
    }

    public function recordLearningTimeAction(Request $request, $lessonId, $time)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $this->getCourseService()->waveLearningTime($user['id'], $lessonId, $time);

        return $this->createJsonResponse(true);
    }

    public function detailDataAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $count     = $this->getCourseService()->getCourseStudentCount($id);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $students = $this->getCourseService()->findCourseStudents($id, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        foreach ($students as $key => $student) {
            $user                       = $this->getUserService()->getUser($student['userId']);
            $students[$key]['nickname'] = $user['nickname'];

            $questionCount                   = $this->getThreadService()->searchThreadCount(array('courseId' => $id, 'type' => 'question', 'userId' => $user['id']));
            $students[$key]['questionCount'] = $questionCount;

            if ($student['learnedNum'] >= $course['lessonNum'] && $course['lessonNum'] > 0) {
                $finishLearn                   = $this->getCourseService()->searchLearns(array('courseId' => $id, 'userId' => $user['id'], 'sttaus' => 'finished'), array('finishedTime', 'DESC'), 0, 1);
                $students[$key]['fininshTime'] = $finishLearn[0]['finishedTime'];

                $students[$key]['fininshDay'] = intval(($finishLearn[0]['finishedTime'] - $student['createdTime']) / (60 * 60 * 24));
            } else {
                $students[$key]['fininshDay'] = intval((time() - $student['createdTime']) / (60 * 60 * 24));
            }

            $learnTime                   = $this->getCourseService()->searchLearnTime(array('userId' => $user['id'], 'courseId' => $id));
            $students[$key]['learnTime'] = $learnTime;
        }

        return $this->render('TopxiaWebBundle:Course:course-data-modal.html.twig', array(
            'course'    => $course,
            'paginator' => $paginator,
            'students'  => $students
        ));
    }

    public function recordWatchingTimeAction(Request $request, $lessonId, $time)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $learn = $this->getCourseService()->waveWatchingTime($user['id'], $lessonId, $time);

        $isLimit = $this->setting('magic.lesson_watch_limit');

        if ($isLimit) {
            $lesson         = $this->getCourseService()->getLesson($lessonId);
            $course         = $this->getCourseService()->getCourse($lesson['courseId']);
            $watchLimitTime = $course['watchLimit'] * $lesson['length'];

            if ($lesson['type'] == 'video' && ($course['watchLimit'] > 0) && ($learn['watchTime'] >= $watchLimitTime)) {
                $learn['watchLimited'] = true;
            }
        }

        return $this->createJsonResponse($learn);
    }

    public function addMemberExpiryDaysAction(Request $request, $courseId, $userId)
    {
        $user   = $this->getUserService()->getUser($userId);
        $course = $this->getCourseService()->getCourse($courseId);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $this->getCourseService()->addMemberExpiryDays($courseId, $userId, $fields['expiryDay']);

            return $this->createJsonResponse(true);
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaWebBundle:CourseStudentManage:set-expiryday-modal.html.twig', array(
            'course'  => $course,
            'user'    => $user,
            'default' => $default
        ));
    }

    /**
     * Block Actions.
     */
    public function headerAction($course, $manage = false)
    {
        $user = $this->getCurrentUser();

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);

        $users = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        if (empty($member)) {
            $member['deadline'] = 0;
            $member['levelId']  = 0;
        }

        $isNonExpired = $this->getCourseService()->isMemberNonExpired($course, $member);

        if ($member['levelId'] > 0) {
            $vipChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId']);
        } else {
            $vipChecked = 'ok';
        }

        if ($this->isBecomeStudentFromCourse($member)
            || $this->isBecomeStudentFromClassroomButExitedClassroom($course, $member, $user)) {
            $canExit = true;
        } else {
            $canExit = false;
        }

        return $this->render('TopxiaWebBundle:Course:header.html.twig', array(
            'course'       => $course,
            'canManage'    => $this->getCourseService()->canManageCourse($course['id']),
            'canExit'      => $canExit,
            'member'       => $member,
            'users'        => $users,
            'manage'       => $manage,
            'isNonExpired' => $isNonExpired,
            'vipChecked'   => $vipChecked,
            'isAdmin'      => $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')
        ));
    }

    private function isBecomeStudentFromCourse($member)
    {
        return isset($member["role"]) && isset($member["joinedType"]) && $member["role"] == 'student' && $member["joinedType"] == 'course';
    }

    private function isBecomeStudentFromClassroomButExitedClassroom($course, $member, $user)
    {
        $classroomMembers     = $this->getClassroomService()->getClassroomMembersByCourseId($course["id"], $user->id);
        $classroomMemberRoles = ArrayToolkit::column($classroomMembers, "role");

        return isset($member["joinedType"]) && $member["joinedType"] == 'classroom' && (empty($classroomMemberRoles) || count($classroomMemberRoles) == 0);
    }

    public function teachersBlockAction($course)
    {
        $users    = $this->getUserService()->findUsersByIds($course['teacherIds']);
        $profiles = $this->getUserService()->findUserProfilesByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Course:teachers-block.html.twig', array(
            'course'   => $course,
            'users'    => $users,
            'profiles' => $profiles
        ));
    }

    public function progressBlockAction($course)
    {
        $user = $this->getCurrentUser();

        $member          = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
        $nextLearnLesson = $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']);

        $progress = $this->calculateUserLearnProgress($course, $member);

        return $this->render('TopxiaWebBundle:Course:progress-block.html.twig', array(
            'course'          => $course,
            'member'          => $member,
            'nextLearnLesson' => $nextLearnLesson,
            'progress'        => $progress
        ));
    }

    public function latestMembersBlockAction($course, $count = 10)
    {
        $students = $this->getCourseService()->findCourseStudents($course['id'], 0, 12);
        $users    = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));

        return $this->render('TopxiaWebBundle:Course:latest-members-block.html.twig', array(
            'students' => $students,
            'users'    => $users
        ));
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {
        $userIds = array();

        foreach ($courses as $key => $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

            $courses[$key]['classroomCount'] = count($classroomIds);

            if (count($classroomIds) > 0) {
                $classroom                  = $this->getClassroomService()->getClassroom($classroomIds[0]);
                $courses[$key]['classroom'] = $classroom;
            }
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:Course:courses-block-{$view}.html.twig", array(
            'courses'      => $courses,
            'users'        => $users,
            'classroomIds' => $classroomIds,
            'mode'         => $mode
        ));
    }

    public function selectAction(Request $request)
    {
        $url         = "";
        $type        = "";
        $classroomId = 0;

        if ($request->query->get('url')) {
            $url = $request->query->get('url');
        }

        if ($request->query->get('type')) {
            $type = $request->query->get('type');
        }

        if ($request->query->get('classroomId')) {
            $classroomId = $request->query->get('classroomId');
        }

        $conditions = array(
            'status'   => 'published',
            'parentId' => 0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions), 5
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions, 'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseIds          = ArrayToolkit::column($courses, 'id');
        $unEnabledCourseIds = $this->getClassroomCourseIds($request, $courseIds);

        $userIds = array();

        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:Course:course-pick.html.twig", array(
            'users'              => $users,
            'url'                => $url,
            'courses'            => $courses,
            'type'               => $type,
            'unEnabledCourseIds' => $unEnabledCourseIds,
            'classroomId'        => $classroomId,
            'paginator'          => $paginator
        ));
    }

    protected function getClassroomCourseIds($request, $courseIds)
    {
        $unEnabledCourseIds = array();

        if ($request->query->get('type') != "classroom") {
            return $unEnabledCourseIds;
        }

        $classroomId = $request->query->get('classroomId');

        foreach ($courseIds as $key => $value) {
            $course     = $this->getCourseService()->getCourse($value);
            $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId($value);

            if ($course && count($classrooms) == 0) {
                unset($courseIds[$key]);
            }
        }

        $unEnabledCourseIds = $courseIds;

        return $unEnabledCourseIds;
    }

    public function relatedCoursesBlockAction($course)
    {
        $courses = $this->getCourseService()->findNormalCoursesByAnyTagIdsAndStatus($course['tags'], 'published', array('rating desc,recommendedTime desc ,createdTime desc', ''), 0, 4);

        return $this->render("TopxiaWebBundle:Course:related-courses-block.html.twig", array(
            'courses'       => $courses,
            'currentCourse' => $course
        ));
    }

    public function rebuyAction(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();

        $this->getCourseService()->removeStudent($courseId, $user['id']);

        return $this->redirect($this->generateUrl('course_show', array('id' => $courseId)));
    }

    public function listViewAction(Request $request, $courseId)
    {
        return $this->render('TopxiaWebBundle:Course:list-view.html.twig', array(

        ));
    }

    public function memberIdsAction(Request $request, $id)
    {
        $ids = $this->getCourseService()->findMemberUserIdsByCourseId($id);

        return $this->createJsonResponse($ids);
    }

    public function qrcodeAction(Request $request, $id)
    {
        $user  = $this->getUserService()->getCurrentUser();
        $host  = $request->getSchemeAndHttpHost();
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'userId'   => $user['id'],
            'data'     => array(
                'url'    => $this->generateUrl('course_show', array('id' => $id), true),
                'appUrl' => "{$host}/mapi_v2/mobile/main#/course/{$id}"
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

    public function orderInfoAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (empty($order)) {
            throw $this->createNotFoundException('订单不存在!');
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);

        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        return $this->render('TopxiaWebBundle:Course:course-order.html.twig', array('order' => $order, 'course' => $course));
    }

    private function _checkLiveCloudSetting($type)
    {
        $courseSetting = $this->setting('course', array());

        if ($type == 'live' && empty($courseSetting['live_course_enabled'])) {
            throw new \RuntimeException('请前往后台开启直播,尝试创建！');
        }

        $client   = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        if (empty($capacity['capacity'])) {
            throw new \RuntimeException('请联系EduSoho官方购买直播教室，然后才能开启直播功能！');
        }

        return true;
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getDiscountService()
    {
        return $this->getServiceKernel()->createService('Discount:Discount.DiscountService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    public function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}
