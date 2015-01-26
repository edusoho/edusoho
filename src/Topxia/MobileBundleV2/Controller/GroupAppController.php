<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class GroupAppController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaMobileBundleV2:Group:index.html.twig', array(
        ));
    }

}
