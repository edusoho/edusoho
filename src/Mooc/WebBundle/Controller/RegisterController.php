<?php
namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\RegisterController as BaseRegisterController;

class RegisterController extends BaseRegisterController
{
    public function checkStaffNoAction(Request $request)
    {
        $staffNo = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        if ($exclude == $staffNo) {
            return $this->validateResult('success', '');
        }

        $result                 = $this->getAuthService()->checkStaffNo($staffNo);
        list($result, $message) = $result;
        return $this->validateResult($result, $message);
    }
}
