<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class CourseSetController extends BaseController
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

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}