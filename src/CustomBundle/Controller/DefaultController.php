<?php

namespace CustomBundle\Controller;

use AppBundle\Controller\BaseController;
use CustomBundle\Biz\Course\CourseService;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $course = $this->getCourseService()->getCustomCourseById(1);
        var_dump($course,2);
        return $this->render('CustomBundle:custom:index.html.twig');
      //  return $this->render('default/index.html.twig');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('CustomBundle:Course:CourseService');
    }
}
