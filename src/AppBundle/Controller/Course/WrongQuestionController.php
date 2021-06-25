<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class WrongQuestionController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        return $this->render('course-manage/wrong-question/index.html.twig', [
            'course' => $course,
            'courseSet' => $courseSet,
        ]);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
