<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\BlockToolkit;
use AppBundle\Common\Exception\AbstractException;
use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\StringToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\BlockService;
use Biz\System\Service\SettingService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/operating/certificate/index.html.twig', array(
        ));
    }
}