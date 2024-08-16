<?php

namespace AppBundle\Controller;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ContractController extends BaseController
{
    public function signMobileAction(Request $request)
    {
        return $this->render('contract/sign-mobile.html.twig');
    }
}
