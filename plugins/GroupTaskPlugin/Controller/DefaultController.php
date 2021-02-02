<?php

namespace GroupTaskPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GroupTaskPlugin:Default:index.html.twig');
    }
}
