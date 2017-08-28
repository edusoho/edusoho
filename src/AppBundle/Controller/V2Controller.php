<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class V2Controller extends BaseController
{
    public function demoAction(Request $request)
    {
        return $this->render('v2/demo/index.html.twig');
    }

    public function modalAction(Request $request)
    {
        return $this->render('v2/demo/modal.html.twig');
    }
}
