<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class UserController extends BaseController
{

    public function headerBlockAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($user, $userProfile);

        return $this->render('TopxiaWebBundle:User:header-block.html.twig', array(
            'user'=>$user,
            'isFollowed'=>$this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']),
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);

        if(in_array('ROLE_TEACHER', $user['roles'])) {
            return $this->_teachAction($user);
        }

        return $this->_learnAction($user);
    }

    public function learnAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        return $this->_learnAction($user);
    }

    public function teachAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        return $this->_teachAction($user);
    }

    public function favoritedAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserFavoritedCourseCount($user['id']),
            10
        );

        $courses = $this->getCourseService()->findUserFavoritedCourses(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'favorited',
        ));
    }

    public function friendAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        return $this->render('TopxiaWebBundle:User:friend.html.twig', array(
            'user' => $user,
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

    private function tryGetUser($id)
    {
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw $this->createNotFoundException();
        }
        return $user;
    }

    private function _learnAction($user)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLearnCourseCount($user['id']),
            10
        );

        $courses = $this->getCourseService()->findUserLearnCourses(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'learn',
        ));
    }

    private function _teachAction($user)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLearnCourseCount($user['id']),
            10
        );

        $courses = $this->getCourseService()->findUserTeachCourses(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'teach',
        ));
    }

}