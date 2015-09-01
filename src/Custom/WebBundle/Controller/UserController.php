<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Controller\UserController as UserBaseController;

class UserController extends UserBaseController
{
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

        return $this->render('CustomWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'favorited',
        ));
    }

    public function teachAction(Request $request, $id)
    {
        $user = $this->tryGetUser($id);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $userProfile['about']= strip_tags($userProfile['about'],'');
        $userProfile['about'] = preg_replace("/ /","",$userProfile['about']);
        $user = array_merge($user, $userProfile);
        return $this->_teachAction($user);
    }
 
    protected function _learnAction($user)
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

        return $this->render('CustomWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'learn',
        ));
    }

    protected function _teachAction($user)
    {
        $conditions = array(
            'userId' => $user['id'],
            'parentId' => 0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserTeachCourseCount($conditions),
            10
        );

        $courses = $this->getCourseService()->findUserTeachCourses(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('CustomWebBundle:User:courses.html.twig', array(
            'user' => $user,
            'courses' => $courses,
            'paginator' => $paginator,
            'type' => 'teach',
        ));
    }
}