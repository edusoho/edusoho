<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class StatisticController extends BaseController
{

    public function indexAction(Request $request)
    { 
        return $this->render('TopxiaAdminBundle:Statistic:index.html.twig');
    }

}