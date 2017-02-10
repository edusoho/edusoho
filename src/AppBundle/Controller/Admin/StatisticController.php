<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class StatisticController extends BaseController
{

    public function indexAction(Request $request)
    { 
        return $this->render('admin/statistic/index.html.twig');
    }

}