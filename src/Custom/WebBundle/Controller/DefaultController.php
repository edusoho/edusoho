<?php

namespace Custom\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CustomWebBundle:Default:index.html.twig', array('name' => $name));
    }
}
