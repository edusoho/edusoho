<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class InformationCollectionController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/marketing/information-collection/index.html.twig', []);
    }

    public function createAction(Request $request)
    {

        return $this->render('admin-v2/marketing/information-collection/create.html.twig', []);
    }
}