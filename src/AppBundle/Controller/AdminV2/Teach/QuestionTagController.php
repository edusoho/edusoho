<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;

class QuestionTagController extends BaseController
{
    public function indexAction()
    {
        return $this->render('admin-v2/teach/question-tag/index.html.twig', [
        ]);
    }
}