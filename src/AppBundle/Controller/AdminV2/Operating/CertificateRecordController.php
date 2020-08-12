<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
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
