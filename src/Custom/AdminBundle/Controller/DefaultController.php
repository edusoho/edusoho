<?php

namespace Custom\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\AdminBundle\Controller\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function helloAction($name)
    {
        return $this->render('CustomAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
