<?php

namespace AppBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;

class ItemBankBindController extends CourseBaseController
{
    public function indexAction($course)
    {
        return $this->render('course/tabs/itemBankBind.html.twig',[
            'course' => $course,
        ]);
    }
}