<?php

namespace Codeages\PluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CodeagesPluginBundle:Default:index.html.twig');
    }
}
