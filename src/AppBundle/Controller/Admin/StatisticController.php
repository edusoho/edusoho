<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

class StatisticController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin/statistic/index.html.twig');
    }
}
