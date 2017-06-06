<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class rewardPointController extends BaseController
{
    public function listAction(Request $request)
    {
        return $this->render('reward-point/list.html.twig', array(
        ));
    }
}
