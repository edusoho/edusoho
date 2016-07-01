<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    public function headerBlockAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $user        = array_merge($user, $userProfile);

        if ($this->getCurrentUser()->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']);
        } else {
            $isFollowed = false;
        }

        // 关注数
        $following = $this->getUserService()->findUserFollowingCount($user['id']);
        // 粉丝数
        $follower = $this->getUserService()->findUserFollowerCount($user['id']);

        return $this->render('TopxiaWebBundle:User:header-block.html.twig', array(
            'user'       => $user,
            'isFollowed' => $isFollowed,
            'following'  => $following,
            'follower'   => $follower
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);

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
            return $this->redirect($this->generateUrl('user_show', array('id' => $user['id'])));
        }
    }

    public function learnAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        return $this->_learnAction($user);
    }

    public function aboutAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        return $this->_aboutAction($user);
    }

    public function teachAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        return $this->_teachAction($user);
    }

    public function learningAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        $classrooms           = array();

        $studentClassrooms = $this->getClassroomService()->searchMembers(array('role' => 'student', 'userId' => $user['id']), array('createdTime', 'desc'), 0, PHP_INT_MAX);
        $auditorClassrooms = $this->getClassroomService()->searchMembers(array('role' => 'auditor', 'userId' => $user['id']), array('createdTime', 'desc'), 0, PHP_INT_MAX);

        $classrooms = array_merge($studentClassrooms, $auditorClassrooms);

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        if (!empty($classroomIds)) {
            $conditions = array(
                'status'       => 'published',
                'showable'     => '1',
                'classroomIds' => $classroomIds
            );

            $paginator = new Paginator(
                $this->get('request'),
                $this->getClassroomService()->searchClassroomsCount($conditions),
                20
            );

            $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            foreach ($classrooms as $key => $classroom) {
                if (empty($classroom['teacherIds'])) {
                    $classroomTeacherIds = array();
                } else {
                    $classroomTeacherIds = $classroom['teacherIds'];
                }

                $teachers                     = $this->getUserService()->findUsersByIds($classroomTeacherIds);
                $classrooms[$key]['teachers'] = $teachers;
            }

            $members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user['id'], $classroomIds);
        } else {
            $paginator = new Paginator(
                $this->get('request'),
                0,
                20
            );
            $members = array();
        }

        return $this->render("TopxiaWebBundle:User:classroom-learning.html.twig", array(
            'paginator'  => $paginator,
            'classrooms' => $classrooms,
            'members'    => $members,
            'user'       => $user
        ));
    }

    public function teachingAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        $conditions           = array(
            'roles'  => array('teacher', 'headTeacher'),
            'userId' => $user['id']
        );
        $classroomMembers = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'desc'), 0, PHP_INT_MAX);

        $classroomIds = ArrayToolkit::column($classroomMembers, 'classroomId');
        if (empty($classroomIds)) {
            $paginator = new Paginator(
                $this->get('request'),
                0,
                20
            );
            $members    = array();
            $classrooms = array();
        } else {
            $conditions = array(
                'status'       => 'published',
                'showable'     => '1',
                'classroomIds' => $classroomIds
            );

            $paginator = new Paginator(
                $this->get('request'),
                $this->getClassroomService()->searchClassroomsCount($conditions),
                20
            );

            $classrooms = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user['id'], $classroomIds);

            foreach ($classrooms as $key => $classroom) {
                if (empty($classroom['teacherIds'])) {
                    $classroomTeacherIds = array();
                } else {
                    $classroomTeacherIds = $classroom['teacherIds'];
                }

                $teachers                     = $this->getUserService()->findUsersByIds($classroomTeacherIds);
                $classrooms[$key]['teachers'] = $teachers;
            }
        }
        return $this->render('TopxiaWebBundle:User:classroom-teaching.html.twig', array(
            'paginator'  => $paginator,
            'classrooms' => $classrooms,
            'members'    => $members,
            'user'       => $user
        ));
    }

    public function favoritedAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);

        $conditions = array(
            'userId' => $user['id']
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseFavoriteCount($conditions),
            20
        );

        $courseFavorites = $this->getCourseService()->searchCourseFavorites(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:User:courses_favorited.html.twig', array(
            'user'            => $user,
            'courseFavorites' => $courseFavorites,
            'paginator'       => $paginator,
            'type'            => 'favorited'
        ));
    }

    public function groupAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        $admins               = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'admin'),
            array('createdTime', "DESC"), 0, 1000
        );
        $owners = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'owner'),
            array('createdTime', "DESC"), 0, 1000
        );
        $members     = array_merge($admins, $owners);
        $groupIds    = ArrayToolkit::column($members, 'groupId');
        $adminGroups = $this->getGroupService()->getGroupsByids($groupIds);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchMembersCount(array('userId' => $user['id'], 'role' => 'member')),
            20
        );

        $members = $this->getGroupService()->searchMembers(array('userId' => $user['id'], 'role' => 'member'), array('createdTime', "DESC"), $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $groupIds = ArrayToolkit::column($members, 'groupId');
        $groups   = $this->getGroupService()->getGroupsByids($groupIds);

        return $this->render('TopxiaWebBundle:User:group.html.twig', array(
            'user'        => $user,
            'type'        => 'group',
            'adminGroups' => $adminGroups,
            'paginator'   => $paginator,
            'groups'      => $groups
        ));
    }

    public function followingAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->findUserFollowingCount($user['id']),
            20
        );

        $followings = $this->getUserService()->findUserFollowing($user['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount());

        if ($followings) {
            $followingIds          = ArrayToolkit::column($followings, 'id');
            $followingUserProfiles = ArrayToolkit::index($this->getUserService()->searchUserProfiles(array('ids' => $followingIds), array('id', 'ASC'), 0, count($followingIds)), 'id');
        }

        $myfollowings = $this->_getUserFollowing();

        return $this->render('TopxiaWebBundle:User:friend.html.twig', array(
            'user'           => $user,
            'paginator'      => $paginator,
            'friends'        => $followings,
            'userProfile'    => $userProfile,
            'myfollowings'   => $myfollowings,
            'allUserProfile' => isset($followingUserProfiles) ? $followingUserProfiles : array(),
            'friendNav'      => 'following'
        ));
    }

    public function followerAction(Request $request, $id)
    {
        $user                 = $this->tryGetUser($id);
        $userProfile          = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about'] = strip_tags($userProfile['about'], '');
        $userProfile['about'] = preg_replace("/ /", "", $userProfile['about']);
        $user                 = array_merge($user, $userProfile);
        $myfollowings         = $this->_getUserFollowing();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->findUserFollowerCount($user['id']),
            20
        );

        $followers = $this->getUserService()->findUserFollowers($user['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount());

        if ($followers) {
            $followerIds          = ArrayToolkit::column($followers, 'id');
            $followerUserProfiles = ArrayToolkit::index($this->getUserService()->searchUserProfiles(array('ids' => $followerIds), array('id', 'ASC'), 0, count($followerIds)), 'id');
        }

        return $this->render('TopxiaWebBundle:User:friend.html.twig', array(
            'user'           => $user,
            'paginator'      => $paginator,
            'friends'        => $followers,
            'userProfile'    => $userProfile,
            'myfollowings'   => $myfollowings,
            'allUserProfile' => isset($followerUserProfiles) ? $followerUserProfiles : array(),
            'friendNav'      => 'follower'
        ));
    }

    public function remindCounterAction(Request $request)
    {
        $user    = $this->getCurrentUser();
        $counter = array('newMessageNum' => 0, 'newNotificationNum' => 0);

        if ($user->isLogin()) {
            $counter['newMessageNum']      = $user['newMessageNum'];
            $counter['newNotificationNum'] = $user['newNotificationNum'];
        }

        return $this->createJsonResponse($counter);
    }

    public function unfollowAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $this->getUserService()->unFollow($user['id'], $id);

        return $this->createJsonResponse(true);
    }

    public function followAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $this->getUserService()->follow($user['id'], $id);

        return $this->createJsonResponse(true);
    }

    public function checkPasswordAction(Request $request)
    {
        $password    = $request->query->get('value');
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            $response = array('success' => false, 'message' => '请先登入');
        }

        if (!$this->getUserService()->verifyPassword($currentUser['id'], $password)) {
            $response = array('success' => false, 'message' => '输入的密码不正确');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function cardShowAction(Request $request, $userId)
    {
        $user        = $this->tryGetUser($userId);
        $currentUser = $this->getCurrentUser();
        $profile     = $this->getUserService()->getUserProfile($userId);
        $isFollowed  = false;

        if ($currentUser->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($currentUser['id'], $userId);
        }

        $user['learningNum']  = $this->getCourseService()->findUserLearnCourseCountNotInClassroom($userId);
        $user['followingNum'] = $this->getUserService()->findUserFollowingCount($userId);
        $user['followerNum']  = $this->getUserService()->findUserFollowerCount($userId);
        $levels               = array();

        if ($this->isPluginInstalled('vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');
        }

        return $this->render('TopxiaWebBundle:User:card-show.html.twig', array(
            'user'       => $user,
            'profile'    => $profile,
            'isFollowed' => $isFollowed,
            'levels'     => $levels,
            'nowTime'    => time()
        ));
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

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

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

            if (isset($formData['email']) && !empty($formData['email'])) {
                $this->getAuthService()->changeEmail($user['id'], null, $formData['email']);
                $this->authenticateUser($this->getUserService()->getUser($user['id']));

                if (!$user['setup']) {
                    $this->getUserService()->setupAccount($user['id']);
                }
            }

            $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

            return $this->redirect($goto);
        }

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        $userFields = ArrayToolkit::index($userFields, 'fieldName');
        $userInfo   = $this->getUserService()->getUserProfile($user['id']);

        return $this->render('TopxiaWebBundle:User:fill-userinfo-fields.html.twig', array(
            'userFields' => $userFields,
            'user'       => $userInfo,
            'goto'       => $goto
        ));
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function tryGetUser($id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        return $user;
    }

    protected function _aboutAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        return $this->render('TopxiaWebBundle:User:about.html.twig', array(
            'user'        => $user,
            'userProfile' => $userProfile,
            'type'        => 'about'
        ));
    }

    protected function _learnAction($user)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLearnCourseCountNotInClassroom($user['id']),
            20
        );

        $courses = $this->getCourseService()->findUserLearnCoursesNotInClassroom(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:User:courses.html.twig', array(
            'user'      => $user,
            'courses'   => $courses,
            'paginator' => $paginator,
            'type'      => 'learn'
        ));
    }

    protected function _teachAction($user)
    {
        $conditions = array(
            'userId'   => $user['id'],
            'parentId' => 0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserTeachCourseCount($conditions),
            20
        );

        $courses = $this->getCourseService()->findUserTeachCourses(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaWebBundle:User:courses.html.twig', array(
            'user'      => $user,
            'courses'   => $courses,
            'paginator' => $paginator,
            'type'      => 'teach'
        ));
    }

    protected function _getUserFollowing()
    {
        $user         = $this->getCurrentUser();
        $followings   = $this->getUserService()->findAllUserFollowing($user['id']);
        $followingIds = ArrayToolkit::column($followings, 'id');
        $myfollowings = $this->getUserService()->filterFollowingIds($user['id'], $followingIds);
        return $myfollowings;
    }

    protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }
}
