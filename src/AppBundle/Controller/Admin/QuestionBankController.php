<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\TreeToolkit;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class QuestionBankController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin/question-bank/index.html.twig', array(
        ));
    }

    public function createAction()
    {

    }
}