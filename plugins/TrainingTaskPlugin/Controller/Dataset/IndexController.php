<?php

namespace TrainingTaskPlugin\Controller\Dataset;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Activity\ActivityActionInterface;

class IndexController extends BaseController
{
    public function indexAction(){
        return $this->render('TrainingTaskPlugin:Activity:Admin/modal.html.twig', array(
            'activity' => [1,2,3],
        ));
    }
}