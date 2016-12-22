<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends CourseBaseController
{
    protected function makeCategoryTree($categories)
    {
        $categories = ArrayToolkit::index($categories, 'id');

        foreach ($categories as &$category) {
            if ($category['parentId'] != '0') {
                $categories[$category['parentId']]['subs'] = $category;
            }
        }

        return $categories;
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

            $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));
            $tagIds = ArrayToolkit::column($tags, 'id');

            $course['tags'] = $this->getTagService()->findTagsByIds($tagIds);
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
        $tagIds = $this->getTagIdsByCourse($course);
        $tags     = $this->getTagService()->findTagsByIds($tagIds);
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
        $tagIds = $this->getTagIdsByCourse($course);
        $tags = $this->getTagService()->findTagsByIds($tagIds);

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
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('非常抱歉，您无权限访问该班级，如有需要请联系客服'), '', 3, $this->generateUrl('homepage'));
            }
        }

        return $this->render('TopxiaWebBundle:Course:info.html.twig', array(
            'course'   => $course,
            'member'   => $member,
        ));
    }

    public function lessonListAction(Request $request, $id)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('非常抱歉，您无权限访问该班级，如有需要请联系客服'), '', 3, $this->generateUrl('homepage'));
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
        $user = $this->getCurrentUser();

        list($course, $member) = $this->buildCourseLayoutData($request, $id);

        if (!array_intersect(array('ROLE_ADMIN','ROLE_SUPER_ADMIN'), $user['roles'])) {
            if ($course['status'] == 'closed' && $member == null) {
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('课程已关闭，3秒后返回首页'), '', 3, $this->generateUrl('homepage'));
            }
        }     

        if ($course['parentId'] && empty($member)) {
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('非常抱歉，您无权限访问该班级，如有需要请联系客服'), '', 3, $this->generateUrl('homepage'));
            }

            $user   = $this->getCurrentUser();
            $member = $this->getCourseService()->becomeStudentByClassroomJoined($id, $user->id);

            if (isset($member["id"])) {
                $course['studentNum']++;
            }
        }


        $this->getCourseService()->hitCourse($id);

        $items = $this->getCourseService()->getCourseItems($course['id']);

        if ('normal' == $course['type']){
            $this->dispatchEvent('course.view',
                new ServiceEvent($course, array('userId' => $user['id'])));
        }
        $allTags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'classroom',
            'ownerId'   => $id
        ));

        $tags = array(
            'tagIds' => ArrayToolkit::column($allTags, 'id'),
            'count'  => count($allTags)
        );

        return $this->render("TopxiaWebBundle:Course:{$course['type']}-show.html.twig", array(
            'course' => $course,
            'member' => $member,
            'items'  => $items,
            'tags'   => $tags
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
        $user        = $this->getCurrentUser();
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_TEACHER') && !$user->hasPermission('admin_course_add')) {
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
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('您不是课程的学员。'));
        }

        if ($member["joinedType"] == "course" && !empty($member['orderId'])) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('有关联的订单，不能直接退出学习。'));
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

            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你好像忘了登录哦？'), null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('课程不存在，或已删除。'));
        }

        if ($course['approval'] == 1 && ($user['approvalStatus'] != 'approved')) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该课程需要通过实名认证，你还没有通过实名认证。'), null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        if (!$this->getCourseService()->canTakeCourse($id)) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('您还不是课程《%courseTitle%》的学员，请先购买或加入学习。', array('%courseTitle%' => $course['title'])), null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        try {
            list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

            if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
            }

            if ($member && $member['levelId'] > 0) {
                if($member['joinedType'] == 'course'){
                    $vipLevelId = $course['vipLevelId'];
                } elseif ($member['joinedType'] == 'classroom') {
                    $classroom = $this->getClassroomService()->getClassroom($member['classroomId']);
                    $vipLevelId = $classroom['vipLevelId'];
                }

                if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $vipLevelId) != 'ok') {
                    return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
                }
            }
        } catch (Exception $e) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('抱歉，未发布课程不能学习！'));
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
        $users = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Course:header.html.twig', array(
            'course'       => $course,
            'users'        => $users
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
            $tagIds = $this->getTagIdsByCourse($course);
            $course['tags'] = $this->getTagService()->findTagsByIds($tagIds);
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
        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));
        
        $course['tags'] = ArrayToolkit::column($tags, 'id');

        $courses = $this->getCourseService()->findNormalCoursesByAnyTagIdsAndStatus($course['tags'], 'published', array('rating desc,recommendedTime desc ,createdTime desc', ''), 0, 4);

        return $this->render("TopxiaWebBundle:Course:related-courses-block.html.twig", array(
            'courses'       => $courses,
            'currentCourse' => $course
        ));
    }

    public function deadlineReachAction(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException($this->trans('不允许未登录访问'));
        }

        $this->getCourseService()->quitCourseByDeadlineReach($user['id'], $courseId);

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
            throw $this->createNotFoundException($this->getServiceKernel()->trans('订单不存在!'));
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);

        if (empty($course)) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('课程不存在，或已删除。'));
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
    
    protected function getTagIdsByCourse($course)
    {
        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'course', 'ownerId' => $course['id']));

        return ArrayToolkit::column($tags, 'id');
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
