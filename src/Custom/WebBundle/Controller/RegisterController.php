<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 11:06
 */

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\RegisterController as BaseRegisterController;

class RegisterController extends BaseRegisterController
{
    public function checkStaffNoAction(Request $request)
    {
        $staffNo = $request->query->get('value');
        $result = $this->getAuthService()->checkStaffNo($staffNo);
        list($result, $message) = $result;
        return $this->validateResult($result, $message);
    }
}