<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class AppController extends BaseController
{
    public function indexAction(Request $request)
    {

    }

    public function installedAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:App:installed.html.twig', array(

        ));
    }

    public function updatesAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:App:updates.html.twig', array(

        ));
    }

    public function logsAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:App:logs.html.twig', array(

        ));
    }
}