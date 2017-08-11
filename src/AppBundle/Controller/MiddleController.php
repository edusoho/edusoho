<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class MiddleController extends BaseController
{
    public function templeteAction(Request $request)
    {
        return $this->render('middle/templete.html.twig');
    }
}