<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/operating/certificate/index.html.twig', [
        ]);
    }
}
