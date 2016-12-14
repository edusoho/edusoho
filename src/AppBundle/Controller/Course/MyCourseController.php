<?php


namespace AppBundle\Controller\Course;


use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class MyCourseController extends BaseController
{

    public function learningAction(Request $request)
    {
        $currentUser = $this->getUser();
        $paginator   = new Paginator(
            $request,
            $this->getCourseService()->countLeaningCourseByUserId($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findLearningCourseByUserId(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my-course/learning.html.twig', array(
            'courses'   => $courses,
            'paginator' => $paginator
        ));

    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}