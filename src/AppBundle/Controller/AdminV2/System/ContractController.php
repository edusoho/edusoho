<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;

class ContractController extends BaseController
{
    public function indexAction()
    {
        return $this->render('admin-v2/system/contract/index.html.twig', [
        ]);
    }
}
