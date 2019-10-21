<?php

namespace AppBundle\Controller\AdminV2;

use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        return $this->render('admin-v2/default/index.html.twig', array(
            'dates' => $weekAndMonthDate,
        ));
    }
}
