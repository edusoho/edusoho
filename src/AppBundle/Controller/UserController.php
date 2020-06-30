<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\SmsToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\Favorite\Service\FavoriteService;
use Biz\Group\Service\GroupService;
use Biz\System\Service\SettingService;
use Biz\User\CurrentUser;
use Biz\User\Service\AuthService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Vip\Service\Vip\LevelService;
use Vip\Service\Vip\VipService;

class UserController extends BaseController
{
    public function headerBlockAction($user)
    {
        if (1 == $user['destroyed']) {
            return $this->render('user/header-destroyed-block.html.twig', ['user' => $user]);
        }
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($user, $userProfile);

        if ($this->getCurrentUser()->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']);
        } else {
            $isFollowed = false;
        }

        // 关注数
        $following = $this->getUserService()->findUserFollowingCount($user['id']);
        // 粉丝数
        $follower = $this->getUserService()->findUserFollowerCount($user['id']);

        return $this->render('user/header-block.html.twig', [
            'user' => $user,
            'isFollowed' => $isFollowed,
            'following' => $following,
            'follower' => $follower,
        ]);
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        if (in_array('ROLE_TEACHER', $user['roles'])) {
            return $this->_teachAction($user);
        }

        return $this->_learnAction($user);
    }

    public function pageShowAction()
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，请先登录！');
        } else {
            return $this->redirect($this->generateUrl('user_show', ['id' => $user['id']]));
        }
    }

    public function learnAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        return $this->_learnAction($user);
    }

    public function aboutAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);

        return $this->_aboutAction($user);
    }

    public function teachAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        return $this->_teachAction($user);
    }

    public function learningAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);
        $classrooms = [];

        $studentClassrooms = $this->getClassroomService()->searchMembers(['role' => 'student', 'userId' => $user['id']], ['createdTime' => 'desc'], 0, PHP_INT_MAX);
        $auditorClassrooms = $this->getClassroomService()->searchMembers(['role' => 'auditor', 'userId' => $user['id']], ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $classrooms = array_merge($studentClassrooms, $auditorClassrooms);

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        if (!empty($classroomIds)) {
            $conditions = [
                'status' => 'published',
                'showable' => '1',
                'classroomIds' => $classroomIds,
            ];

            $paginator = new Paginator(
                $this->get('request'),
                $this->getClassroomService()->countClassrooms($conditions),
                20
            );

            $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                ['createdTime' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            foreach ($classrooms as $key => $classroom) {
                if (empty($classroom['teacherIds'])) {
                    $classroomTeacherIds = [];
                } else {
                    $classroomTeacherIds = $classroom['teacherIds'];
                }

                $teachers = $this->getUserService()->findUsersByIds($classroomTeacherIds);
                $classrooms[$key]['teachers'] = $teachers;
            }
        } else {
            $paginator = new Paginator(
                $this->get('request'),
                0,
                20
            );
        }

        return $this->render('user/classroom-learning.html.twig', [
            'paginator' => $paginator,
            'classrooms' => $classrooms,
            'user' => $user,
        ]);
    }

    public function teachingAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);
        $conditions = [
            'roles' => ['teacher', 'headTeacher'],
            'userId' => $user['id'],
        ];
        $classroomMembers = $this->getClassroomService()->searchMembers($conditions, ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $classroomIds = ArrayToolkit::column($classroomMembers, 'classroomId');
        if (empty($classroomIds)) {
            $paginator = new Paginator(
                $this->get('request'),
                0,
                20
            );
            $classrooms = [];
        } else {
            $conditions = [
                'status' => 'published',
                'showable' => '1',
                'classroomIds' => $classroomIds,
            ];

            $paginator = new Paginator(
                $this->get('request'),
                $this->getClassroomService()->countClassrooms($conditions),
                20
            );

            $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                ['createdTime' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            foreach ($classrooms as $key => $classroom) {
                if (empty($classroom['teacherIds'])) {
                    $classroomTeacherIds = [];
                } else {
                    $classroomTeacherIds = $classroom['teacherIds'];
                }

                $teachers = $this->getUserService()->findUsersByIds($classroomTeacherIds);
                $classrooms[$key]['teachers'] = $teachers;
            }
        }

        return $this->render('user/classroom-teaching.html.twig', [
            'paginator' => $paginator,
            'classrooms' => $classrooms,
            'user' => $user,
        ]);
    }

    public function favoritedAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        $conditions = [
            'userId' => $user['id'],
            'targetTypes' => ['course', 'openCourse'],
        ];

        $paginator = new Paginator(
            $this->get('request'),
            $this->getFavoriteService()->countFavorites($conditions),
            20
        );

        $favorites = $this->getFavoriteService()->searchFavorites(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('user/courses_favorited.html.twig', [
            'user' => $user,
            'courseFavorites' => $favorites,
            'paginator' => $paginator,
            'type' => 'favorited',
        ]);
    }

    public function groupAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);
        $admins = $this->getGroupService()->searchMembers(['userId' => $user['id'], 'role' => 'admin'],
            ['createdTime' => 'DESC'], 0, 1000
        );
        $owners = $this->getGroupService()->searchMembers(['userId' => $user['id'], 'role' => 'owner'],
            ['createdTime' => 'DESC'], 0, 1000
        );
        $members = array_merge($admins, $owners);
        $groupIds = ArrayToolkit::column($members, 'groupId');
        $adminGroups = $this->getGroupService()->getGroupsByIds($groupIds);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->countMembers(['userId' => $user['id'], 'role' => 'member']),
            20
        );

        $members = $this->getGroupService()->searchMembers(['userId' => $user['id'], 'role' => 'member'], ['createdTime' => 'DESC'], $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $groupIds = ArrayToolkit::column($members, 'groupId');
        $groups = $this->getGroupService()->getGroupsByids($groupIds);

        return $this->render('user/group.html.twig', [
            'user' => $user,
            'type' => 'group',
            'adminGroups' => $adminGroups,
            'paginator' => $paginator,
            'groups' => $groups,
        ]);
    }

    public function followingAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->findUserFollowingCount($user['id']),
            20
        );

        $followings = $this->getUserService()->findUserFollowing($user['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount());

        if ($followings) {
            $followingIds = ArrayToolkit::column($followings, 'id');
            $followingUserProfiles = ArrayToolkit::index($this->getUserService()->searchUserProfiles(['ids' => $followingIds], ['id' => 'ASC'], 0, count($followingIds)), 'id');
        }

        $myfollowings = $this->_getUserFollowing();

        return $this->render('user/friend.html.twig', [
            'user' => $user,
            'paginator' => $paginator,
            'friends' => $followings,
            'userProfile' => $userProfile,
            'myfollowings' => $myfollowings,
            'allUserProfile' => isset($followingUserProfiles) ? $followingUserProfiles : [],
            'friendNav' => 'following',
        ]);
    }

    public function followerAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace('/ /', '', $userProfile['about']);
        $user = array_merge($user, $userProfile);
        $myfollowings = $this->_getUserFollowing();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->findUserFollowerCount($user['id']),
            20
        );

        $followers = $this->getUserService()->findUserFollowers($user['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount());

        if ($followers) {
            $followerIds = ArrayToolkit::column($followers, 'id');
            $followerUserProfiles = ArrayToolkit::index($this->getUserService()->searchUserProfiles(['ids' => $followerIds], ['id' => 'ASC'], 0, count($followerIds)), 'id');
        }

        return $this->render('user/friend.html.twig', [
            'user' => $user,
            'paginator' => $paginator,
            'friends' => $followers,
            'userProfile' => $userProfile,
            'myfollowings' => $myfollowings,
            'allUserProfile' => isset($followerUserProfiles) ? $followerUserProfiles : [],
            'friendNav' => 'follower',
        ]);
    }

    public function remindCounterAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $counter = ['newMessageNum' => 0, 'newNotificationNum' => 0];

        if ($user->isLogin()) {
            $counter['newMessageNum'] = $user['newMessageNum'];
            $counter['newNotificationNum'] = $user['newNotificationNum'];
        }

        return $this->createJsonResponse($counter);
    }

    public function unfollowAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $this->getUserService()->unFollow($user['id'], $id);

        return $this->createJsonResponse(true);
    }

    public function followAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $this->getUserService()->follow($user['id'], $id);

        return $this->createJsonResponse(true);
    }

    public function checkPasswordAction(Request $request)
    {
        $password = $request->query->get('value');
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            $response = ['success' => false, 'message' => '请先登入'];
        }

        if (!$this->getUserService()->verifyPassword($currentUser['id'], $password)) {
            $response = ['success' => false, 'message' => '输入的密码不正确'];
        } else {
            $response = ['success' => true, 'message' => ''];
        }

        return $this->createJsonResponse($response);
    }

    public function cardShowAction(Request $request, $userId)
    {
        $user = $this->tryGetUser($userId);
        $currentUser = $this->getCurrentUser();
        $profile = $this->getUserService()->getUserProfile($userId);
        $isFollowed = false;

        if ($currentUser->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($currentUser['id'], $userId);
        }

        $user['learningNum'] = $this->getCourseService()->countUserLearningCourses($userId);
        $user['followingNum'] = $this->getUserService()->findUserFollowingCount($userId);
        $user['followerNum'] = $this->getUserService()->findUserFollowerCount($userId);
        $levels = [];

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(['enabled' => 1], null, 0, 100), 'id');
        }

        return $this->render('user/card-show.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'isFollowed' => $isFollowed,
            'levels' => $levels,
            'nowTime' => time(),
        ]);
    }

    public function fillUserInfoAction(Request $request)
    {
        $auth = $this->getSettingService()->get('auth');
        $user = $this->getCurrentUser();

        if ($auth['fill_userinfo_after_login'] && !isset($auth['registerSort'])) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！');
        }

        $goto = $this->getTargetPath($request);

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $authSetting = $this->setting('auth', []);

            if (!empty($formData['mobile']) && !empty($authSetting['mobileSmsValidate'])) {
                list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, 'sms_bind');

                if (!$result) {
                    return $this->createMessageResponse('info', 'register.userinfo_fill_tips', '', 3, $this->generateUrl('login_after_fill_userinfo'));
                }
            }

            $userInfo = $this->saveUserInfo($request, $user);

            return $this->redirect($goto);
        }

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $userFields = ArrayToolkit::index($userFields, 'fieldName');
        $userInfo = $this->getUserService()->getUserProfile($user['id']);

        return $this->render('user/fill-userinfo-fields.html.twig', [
            'userFields' => $userFields,
            'user' => $userInfo,
            'goto' => $goto,
        ]);
    }

    public function fillInfoWhenBuyAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！');
        }

        $this->saveUserInfo($request, $user);

        /**
         * 这里要重构,这段代码是多余了，为了兼容点击任务预览跳转支付页面
         * TODO
         */
        $courseId = $request->request->get('courseId', 0);
        if ($courseId) {
            $this->getCourseService()->tryFreeJoin($courseId);
            $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
            if ($member) {
                return $this->createJsonResponse([
                    'url' => $this->generateUrl('my_course_show', ['id' => $courseId]),
                ]);
            } else {
                return $this->createJsonResponse([
                    'url' => $this->generateUrl('order_show', ['targetId' => $courseId, 'targetType' => 'course']),
                ]);
            }
        }
        /* end todo */

        return $this->createJsonResponse([
            'msg' => 'success',
        ]);
    }

    public function stickCourseSetAction(Request $request, $courseSetId)
    {
        $this->getCourseMemberService()->stickMyCourseByCourseSetId($courseSetId);

        return $this->createJsonResponse(true);
    }

    public function unStickCourseSetAction(Request $request, $courseSetId)
    {
        $this->getCourseMemberService()->unStickMyCourseByCourseSetId($courseSetId);

        return $this->createJsonResponse(true);
    }

    protected function saveUserInfo($request, $user)
    {
        $formData = $request->request->all();

        $userInfo = ArrayToolkit::parts($formData, [
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
            'textField1', 'textField2', 'textField3', 'textField4', 'textField5', 'textField6', 'textField7', 'textField8', 'textField9', 'textField10',
        ]);

        if (isset($formData['email']) && !empty($formData['email'])) {
            $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
        }

        $authSetting = $this->setting('auth', []);
        if (!empty($formData['mobile']) && !empty($authSetting['fill_userinfo_after_login']) && !empty($authSetting['mobileSmsValidate'])) {
            $verifiedMobile = $formData['mobile'];
            $this->getUserService()->changeMobile($user['id'], $verifiedMobile);
        }

        $currentUser = new CurrentUser();
        $currentUser->fromArray($this->getUserService()->getUser($user['id']));
        $this->switchUser($request, $currentUser);

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

        return $userInfo;
    }

    protected function tryGetUser($id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        return $user;
    }

    protected function _aboutAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        return $this->render('user/about.html.twig', [
            'user' => $user,
            'userProfile' => $userProfile,
            'type' => 'about',
        ]);
    }

    protected function _learnAction($user)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserLearnCourseSets($user['id']),
            20
        );

        $courseSets = $this->getCourseSetService()->searchUserLearnCourseSets(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('user/course-sets.html.twig', [
            'user' => $user,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
            'type' => 'learn',
        ]);
    }

    protected function _teachAction($user)
    {
        $conditions = [
            'status' => 'published',
            'parentId' => 0,
        ];
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $sets = $this->getCourseSetService()->searchCourseSetsByTeacherOrderByStickTime(
            $conditions,
            ['createdTime' => 'DESC'],
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $sets = ArrayToolkit::index($sets, 'id');

        //这里迫于当前逻辑是未发布计划也会算在内，所以这里没有找courseIds也没有加published条件
//        $teachedCourseIds = $this->getCourseService()->searchCourses(
//            array('userId' => $user['id']),
//            array(),
//            0,
//            PHP_INT_MAX,
//            array('courseId')
//        );

        $setIds = ArrayToolkit::column($sets, 'id');

        $stickSeqCourseSetMembers = $this->getCourseMemberService()->searchMembers(
            ['userId' => $user['id'], 'courseSetIds' => $setIds],
            ['stickyTime' => 'DESC', 'createdTime' => 'DESC'],
            0,
            PHP_INT_MAX,
            ['courseSetId', 'stickyTime']
        );

        foreach ($stickSeqCourseSetMembers as $stickSeqCourseSetMember) {
            if (!empty($stickSeqCourseSetMember['stickyTime']) && isset($sets[$stickSeqCourseSetMember['courseSetId']])) {
                $sets[$stickSeqCourseSetMember['courseSetId']]['stickyTime'] = $stickSeqCourseSetMember['stickyTime'];
            }
        }

        if (count($sets) > 1) {
            $stickSeqCourseSetIds = ArrayToolkit::column($stickSeqCourseSetMembers, 'courseSetId');

            usort($sets, function ($a, $b) use ($stickSeqCourseSetIds) {
                return (array_search($a['id'], $stickSeqCourseSetIds) < array_search($b['id'], $stickSeqCourseSetIds)) ? -1 : 1;
            });
        }

        return $this->render('user/course-sets.html.twig', [
            'user' => $user,
            'courseSets' => $sets,
            'paginator' => $paginator,
            'type' => 'teach',
        ]);
    }

    protected function _getUserFollowing()
    {
        $user = $this->getCurrentUser();
        $followings = $this->getUserService()->findAllUserFollowing($user['id']);
        $followingIds = ArrayToolkit::column($followings, 'id');
        $myfollowings = $this->getUserService()->filterFollowingIds($user['id'], $followingIds);

        return $myfollowings;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->getBiz()->service('Group:GroupService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->getBiz()->service('Course:CourseNoteService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->getBiz()->service('Favorite:FavoriteService');
    }
}
