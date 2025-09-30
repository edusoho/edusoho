<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ContractController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('my/contract/contract.html.twig');
    }
}
