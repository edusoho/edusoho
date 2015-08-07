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
}