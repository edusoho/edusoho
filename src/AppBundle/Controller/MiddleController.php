<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class MiddleController extends BaseController
{
    public function templeteAction(Request $request)
    {
        return $this->render('middle/templete/templete.html.twig');
    }

    public function modalAction(Request $request)
    {
        return $this->render('middle/templete/modal.html.twig');
    }
}