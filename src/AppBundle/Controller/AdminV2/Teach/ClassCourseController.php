<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ClassCourseController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/teach/class-course/index.html.twig', array(
        ));
    }
}