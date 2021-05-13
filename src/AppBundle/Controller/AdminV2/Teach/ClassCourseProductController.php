<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ClassCourseProductController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/teach/class-course-product/index.html.twig', [
        ]);
    }
}
