<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class V2Controller extends BaseController
{
    public function templeteAction(Request $request)
    {
        return $this->render('v2/templete/templete.html.twig');
    }

    public function modalAction(Request $request)
    {
        return $this->render('v2/templete/modal.html.twig');
    }
}
