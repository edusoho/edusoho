<?php

namespace TrainingTaskPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TrainingTaskPlugin:Default:index.html.twig');
    }
}
