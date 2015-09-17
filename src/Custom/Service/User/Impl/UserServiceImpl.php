<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 13:59
 */

namespace Custom\Service\User\Impl;
use Custom\Service\User\UserService;
use Topxia\Common\SimpleValidator;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\User\Impl\UserServiceImpl as BaseUserServiceImpl;


class UserServiceImpl extends BaseUserServiceImpl implements UserService
{
    public function getUserByStaffNo($staffNo)
    {
        if(empty($staffNo)){
            return null;
        }

        $user = $this->getUserDao()->getUserByStaffNo($staffNo);

        return $user ? $this->unserialize($user) : null;
    }

    public function register($registration, $type = 'default')
    {
        if (!SimpleValidator::nickname($registration['nickname'])) {
            throw $this->createServiceException('nickname error!');
        }

        if (!$this->isNicknameAvaliable($registration['nickname'])) {
            throw $this->createServiceException('昵称已存在');
        }

        if (!SimpleValidator::email($registration['email'])) {
            throw $this->createServiceException('email error!');
        }
        if (!$this->isEmailAvaliable($registration['email'])) {
            throw $this->createServiceException('Email已存在');
        }

        $user = array();
        if (isset($registration['verifiedMobile'])) {
            $user['verifiedMobile'] = $registration['verifiedMobile'];
        } else {
            $user['verifiedMobile'] = '';
        }

        $user['email'] = $registration['email'];
        $user['emailVerified'] = isset($registration['emailVerified']) ? $registration['emailVerified'] : 0;
        $user['nickname'] = $registration['nickname'];
        $user['roles'] =  array('ROLE_USER');
        $user['type'] = isset($registration['type']) ? $registration['type'] : $type;
        $user['createdIp'] = empty($registration['createdIp']) ? '' : $registration['createdIp'];
        $user['createdTime'] = time();
        $user['staffNo']  = empty($registration['staffNo']) ? '' : $registration['staffNo'];

        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());
        if (in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 1;
        } elseif (in_array($type, array('qq', 'weibo', 'renren','weixinweb')) && isset($thirdLoginInfo["{$type}_set_fill_account"]) && $thirdLoginInfo["{$type}_set_fill_account"]){
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 1;
        }else {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 0;
        }
        $user = $this->unserialize(
            $this->getUserDao()->addUser($this->serialize($user))
        );

        if (isset($registration['mobile']) && $registration['mobile'] != "" && !SimpleValidator::mobile($registration['mobile'])) {
            throw $this->createServiceException('mobile error!');
        }

        if (isset($registration['idcard']) && $registration['idcard'] != "" && !SimpleValidator::idcard($registration['idcard'])) {
            throw $this->createServiceException('idcard error!');
        }

        if (isset($registration['truename']) && $registration['truename'] != "" && !SimpleValidator::truename($registration['truename'])) {
            throw $this->createServiceException('truename error!');
        }

        $profile = array();
        $profile['id'] = $user['id'];
        $profile['mobile'] = empty($registration['mobile']) ? '' : $registration['mobile'];
        $profile['idcard'] = empty($registration['idcard']) ? '' : $registration['idcard'];
        $profile['truename'] = empty($registration['truename']) ? '' : $registration['truename'];
        $profile['company'] = empty($registration['company']) ? '' : $registration['company'];
        $profile['job'] = empty($registration['job']) ? '' : $registration['job'];
        $profile['weixin'] = empty($registration['weixin']) ? '' : $registration['weixin'];
        $profile['weibo'] = empty($registration['weibo']) ? '' : $registration['weibo'];
        $profile['qq'] = empty($registration['qq']) ? '' : $registration['qq'];
        $profile['site'] = empty($registration['site']) ? '' : $registration['site'];
        $profile['gender'] = empty($registration['gender']) ? 'secret' : $registration['gender'];
        for ($i = 1;$i <= 5;$i++) {
            $profile['intField'.$i] = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i] = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }
        for ($i = 1;$i <= 10;$i++) {
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? "" : $registration['varcharField'.$i];
            $profile['textField'.$i] = empty($registration['textField'.$i]) ? "" : $registration['textField'.$i];
        }

        $this->getProfileDao()->addProfile($profile);
        if ($type != 'default') {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }

        $this->getDispatcher()->dispatch('user.service.registered', new ServiceEvent($user));

        return $user;
    }

    public function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' :  '|' . implode('|', $user['roles']) . '|';
        return $user;
    }

    public function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }
        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|')) ;
        return $user;
    }

    public function unserializes(array $users)
    {
        return array_map(function($user) {
            return $this->unserialize($user);
        }, $users);
    }

}