<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ContractController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/system/contract/index.html.twig', [
        ]);
    }

}
