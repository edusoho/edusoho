<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\User\Service\MobileMaskService;
use Symfony\Component\HttpFoundation\Request;

class MobileMaskController extends BaseController
{
    public function showMobileAction(Request $request)
    {
        $encryptedMobile = $request->request->get('encryptedMobile');

        return $this->createJsonResponse([
            'mobile' => $this->getMobileMaskService()->decryptMobile($encryptedMobile),
        ]);
    }

    /**
     * @return MobileMaskService
     */
    protected function getMobileMaskService()
    {
        return $this->createService('User:MobileMaskService');
    }
}
