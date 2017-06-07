<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class awardPointController extends BaseController
{
    public function listAction(Request $request)
    {
        return $this->render('award-point/list.html.twig', array(
        ));
    }

    public function detailAction(Request $request)
    {
        return $this->render('award-point/product-detail.html.twig', array(
        ));
    }
}
