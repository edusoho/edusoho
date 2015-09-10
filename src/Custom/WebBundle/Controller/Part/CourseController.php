<?php
namespace Custom\WebBundle\Controller\Part;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

use Topxia\WebBundle\Controller\Part\CourseController as BaseCourseController;

class CourseController extends BaseCourseController
{

    public function otherPeriodsAction($course){
        $course = $this->getCourse($course);
        $otherPeriods = $this->getCourseService()->findOtherPeriods($course['id']);
        
        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-other-periods.html.twig', array(
            'course' => $course,
            'otherPeriods' => $otherPeriods,
        ));
    }

}

