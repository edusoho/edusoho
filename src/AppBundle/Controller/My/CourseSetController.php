<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\Course\CourseBaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class CourseSetController extends CourseBaseController
{
    public function favoriteAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserFavorites($user['id']),
            12
        );


        $courseFavorites = $this->getCourseSetService()->searchUserFavorites(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my/learning/course-set/favorite.html.twig', array(
            'courseFavorites' => $courseFavorites,
            'paginator'       => $paginator
        ));
    }

    public function teachingAction(Request $request, $filter = 'normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = array(
            'type' => 'normal'
        );

        if ($filter == 'live') {
            $conditions['type'] = 'live';
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $sets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $service = $this->getCourseService();
        $sets    = array_map(function ($set) use ($user, $service) {
            $set['canManage'] = $set['creator'] == $user['id'];
            $set['courses']   = $service->findUserTeachingCoursesByCourseSetId($set['id'], false);
            return $set;
        }, $sets);

        return $this->render('my/teaching/course-sets.html.twig', array(
            'courseSets' => $sets,
            'paginator'  => $paginator,
            'filter'     => $filter
        ));
    }
}