<?php

namespace AppBundle\Controller;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\InfoSecurity\Service\MobileMaskService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class MobileMaskController extends BaseController
{
    public function showMobileAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }
        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
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
        return $this->createService('InfoSecurity:MobileMaskService');
    }
}
