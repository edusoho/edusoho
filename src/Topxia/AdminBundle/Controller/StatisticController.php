<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceException;

class StatisticController extends BaseController
{

    public function indexAction(Request $request)
    { 
        return $this->render('TopxiaAdminBundle:Statistic:index.html.twig');
    }

}