<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Controller\BaseController;

class ManageController extends BaseController
{
    public function indexAction()
    {
        return $this->render('question-bank/list.html.twig', array(
        ));
    }

    public function manageAction()
    {
    }
}
