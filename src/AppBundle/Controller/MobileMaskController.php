<?php

namespace AppBundle\Controller;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\User\Service\MobileMaskService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class MobileMaskController extends BaseController
{
    public function showMobileAction(Request $request)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
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
