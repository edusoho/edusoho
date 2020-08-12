<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CertificateRecordController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/operating/certificate-member/index.html.twig', [
            'targetType' => $request->query->get('targetType'),
        ]);
    }
}