<?php

namespace Custom\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function helloAction($name)
    {
        return $this->render('CustomAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
