<?php
namespace Topxia\WebBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class UserController extends BaseController
{

    public function showAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $teachingCount = $this->getCourseService()->searchMemberCount(array('userId' => $user['id'], 'role'=>'teacher'));
        $learningMembers = $this->getCourseService()->searchMember(array('userId' => $user['id'], 'role'=>'student'),0,10);
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($learningMembers, 'courseId'));

        $followInfo = $this->getFollowInfo($user['id']);

        return $this->render('TopxiaWebBundle:User:show.html.twig', array(
            'isFollowed'=>$this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']),
            'userProfile'=>$userProfile,
            'user'=>$user,
            'teachingCount'=>$teachingCount,
            'courses'=>$courses,
            'users'=>$followInfo['users'],
            'followings'=>$followInfo['followings'],
            'followers'=>$followInfo['followers']
        ));
    }

    public function teachingCoursesAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $teachingMembers = $this->getCourseService()->searchMember(array('userId' => $user['id'], 'role'=>'teacher'),0,10);
        $teachingCount = $this->getCourseService()->searchMemberCount(array('userId' => $user['id'], 'role'=>'teacher'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($teachingMembers, 'courseId'));
        $followInfo = $this->getFollowInfo($user['id']);
        return $this->render('TopxiaWebBundle:User:teachings.html.twig', array(
            'isFollowed'=>$this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']),
            'userProfile'=>$userProfile,
            'user'=>$user,
            'teachingCount'=>$teachingCount,
            'courses'=>$courses,
            'users'=>$followInfo['users'],
            'followings'=>$followInfo['followings'],
            'followers'=>$followInfo['followers']
        ));
    }

    public function favoriteCoursesAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $teachingCount = $this->getCourseService()->searchMemberCount(array('userId' => $user['id'], 'role'=>'teacher'));
        $courses = $this->getCourseService()->findUserFavoriteCourses($user['id'], 0 , 10);
        
        $followInfo = $this->getFollowInfo($user['id']);
        return $this->render('TopxiaWebBundle:User:favorites.html.twig', array(
            'isFollowed'=>$this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']),
            'userProfile'=>$userProfile,
            'user'=>$user,
            'teachingCount'=>$teachingCount,
            'users'=>$followInfo['users'],
            'followings'=>$followInfo['followings'],
            'followers'=>$followInfo['followers'],
            'courses'=>$courses
        ));
    }

    public function remindCounterAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $counter = array('newMessageNum' => 0, 'newNotificationNum' => 0);
        if ($user->isLogin()) {
            $counter['newMessageNum'] = $user['newMessageNum'];
            $counter['newNotificationNum'] = $user['newNotificationNum'];
        }
        return $this->createJsonResponse($counter);
    }

    public function unfollowAction(Request $request, $id)
    {
        try {
            $user = $this->getCurrentUser();
            $this->getUserService()->unFollow($user['id'], $id);
        } catch (Exception $e) {
            return $this->createJsonResponse(false);
        }
        return $this->createJsonResponse(true);
    }

    public function followAction(Request $request, $id)
    {
        try {
            $user = $this->getCurrentUser();
            $this->getUserService()->follow($user['id'], $id);
        } catch (Exception $e) {
            return $this->createJsonResponse(false);
        }
        return $this->createJsonResponse(true);
    }

    private function getFollowInfo($userId)
    {
        $followings = $this->getUserService()->findUserFollowing($userId);
        $followers = $this->getUserService()->findUserFollowers($userId);
        $followingIds = ArrayToolkit::column($followings, 'toId');
        $followerIds = ArrayToolkit::column($followers, 'fromId');
        $userIds = array_merge($followingIds, $followerIds);
        $users = $this->getUserService()->findUsersByIds($userIds);
        return array('followings'=>$followings, 'followers'=>$followers, 'users'=>$users);
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


}