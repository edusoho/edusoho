<?php

namespace CustomBundle\Controller;

use AppBundle\Controller\BaseController;
use CustomBundle\Biz\Course\Service\CourseService;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $this->getCustomCourseService()->getCustomCourseById(1);
        $this->getCourseService()->getCustomCourseById(1);
        $this->getCourseService()->getCourse(1);
        return $this->render('CustomBundle:custom:index.html.twig');
        //  return $this->render('default/index.html.twig');
    }

    /**
     * 使用自己的业务
     * @return CourseService
     */
    protected function getCustomCourseService()
    {
        return $this->getBiz()->service('CustomBundle:Course:CourseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
