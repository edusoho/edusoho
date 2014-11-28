<?php

namespace fomalhaut\AcctSetupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AcctSetupBundle:Default:index.html.twig', array('name' => $name));
    }
}
