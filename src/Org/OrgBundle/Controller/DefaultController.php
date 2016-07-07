<?php

namespace Org\OrgBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OrgBundle:Default:index.html.twig', array('name' => $name));
    }
}
