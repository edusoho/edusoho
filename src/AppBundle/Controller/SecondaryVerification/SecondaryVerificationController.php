<?php

namespace AppBundle\Controller\SecondaryVerification;

use AppBundle\Controller\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class SecondaryVerificationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $params = $request->query->all();
        $operateUser = $this->getUser();
        $result = CloudAPIFactory::create('leaf')->get('/me');

        return $this->render(
            'secondary-verification/secondary-verification-modal.html.twig',
            [
                'exportFileName' => $params['exportFileName'],
                'targetFormId' => $params['targetFormId'],
//                'mobile' => $result['mobile']
                'mobile' => '15925602888',
            ]
        );
    }
}
