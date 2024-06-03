<?php

namespace AppBundle\Controller\AdminV2;

use Symfony\Component\HttpFoundation\Request;

class AIController extends BaseController
{
    public function surveyAction()
    {
        return $this->render('admin-v2/ai/survey-modal.html.twig');
    }

    public function likeAction(Request $request)
    {
        return $this->createJsonResponse(['ok' => true]);
    }
}
