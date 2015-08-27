<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Util\AvatarAlert;
use Topxia\WebBundle\Controller\MyCourseController as BaseMyCourseController;

class MyCourseController extends BaseMyCourseController
{

    public function learningAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLeaningCourseCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeaningCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('CustomWebBundle:MyCourse:learning.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator
        ));
    }

    public function favoritedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserFavoritedCourseCount($currentUser['id']),
            12
        );
        
        $courses = $this->getCourseService()->findUserFavoritedCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();
        foreach ($courses as $favoriteCourse) {
            $userIds = array_merge($userIds, $favoriteCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('CustomWebBundle:MyCourse:favorited.html.twig', array(
            'courses'=>$courses,
            'users'=>$users,
            'paginator' => $paginator
        ));
    }
}