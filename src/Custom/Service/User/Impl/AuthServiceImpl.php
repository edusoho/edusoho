<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 13:50
 */

namespace Custom\Service\User\Impl;

use Topxia\Service\User\Impl\AuthServiceImpl as BaseAuthServiceImpl;
use Custom\Service\User\AuthService;

class AuthServiceImpl extends BaseAuthServiceImpl implements AuthService
{
    public function checkStaffNo($staffNo)
    {

        $user = $this->getUserService()->getUserByStaffNo($staffNo);
        if(empty($user)) {
            return array('success', '');
        }
        return array('error_duplicate', '学号或教工号已存在!');
    }

}