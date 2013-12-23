<?php

namespace Custom\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CustomAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
