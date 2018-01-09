<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class DataLabController extends BaseController
{
    public function dataAction()
    {
        return $this->render('admin/data-lab/data.html.twig');
    }

    public function setttingAction()
    {
        return $this->render('admin/data-lab/setting.html.twig');
    }
}