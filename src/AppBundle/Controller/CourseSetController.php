<?php

namespace AppBundle\Controller;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getFirstPublishedCourseByCourseSetId($id);
        if (empty($course)) {
            throw $this->createNotFoundException('No Avaliable Course in CourseSet#{$id}');
        }

        return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
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
