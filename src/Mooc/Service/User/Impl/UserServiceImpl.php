<?php
namespace Mooc\Service\User\Impl;

use Topxia\Common\ArrayToolkit;
use Mooc\Common\SimpleValidator;
use Mooc\Service\User\UserService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\User\Impl\UserServiceImpl as BaseUserServiceImpl;

class UserServiceImpl extends BaseUserServiceImpl implements UserService
{
    public function getUserByStaffNo($staffNo)
    {
        if (empty($staffNo)) {
            return null;
        }

        $user = $this->getUserDao()->getUserByStaffNo($staffNo);

        return $user ? $this->unserialize($user) : null;
    }

    public function resetUserOrganizationId($organizationId)
    {
        $this->getUserDao()->resetUserOrganizationId($organizationId);
    }

    public function updateUserStaffNo($staffNo, $userId)
    {
        $user = $this->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，学号或教工号更新失败！');
        }

        $fields = array('staffNo' => $staffNo);
        $this->getUserDao()->updateUser($user['id'], $fields);
    }

    public function getUserByTrueName($trueName, $isNeedProfile = false)
    {
        $conditions = array(
            'truename' => $trueName
        );
        $orderBy  = array('id', 'DESC');
        $profiles = $this->searchUserProfiles($conditions, $orderBy, 0, 1);

        $profile = array_shift($profiles);

        if (empty($profile)) {
            return array();
        }

        $user = $this->getUser($profile['id']);

        if ($isNeedProfile) {
            array_push($user, array('profile' => $profile));
        }

        return $user;
    }

    public function updateUserProfile($id, $fields)
    {
        $user = $this->getUser($id);

        if (empty($user)) {
            throw $this->createServiceException('用户不存在，更新用户失败。');
        }

        $fields = ArrayToolkit::filter($fields, array(
            'truename'       => '',
            'gender'         => 'secret',
            'iam'            => '',
            'idcard'         => '',
            'birthday'       => null,
            'city'           => '',
            'mobile'         => '',
            'qq'             => '',
            'school'         => '',
            'class'          => '',
            'staffNo'        => '',
            'organizationId' => 0,
            'company'        => '',
            'job'            => '',
            'signature'      => '',
            'title'          => '',
            'about'          => '',
            'weibo'          => '',
            'weixin'         => '',
            'site'           => '',
            'smallAvatar'    => '',
            'mediumAvatar'   => '',
            'largeAvatar'    => '',
            'intField1'      => null,
            'intField2'      => null,
            'intField3'      => null,
            'intField4'      => null,
            'intField5'      => null,
            'dateField1'     => null,
            'dateField2'     => null,
            'dateField3'     => null,
            'dateField4'     => null,
            'dateField5'     => null,
            'floatField1'    => null,
            'floatField2'    => null,
            'floatField3'    => null,
            'floatField4'    => null,
            'floatField5'    => null,
            'textField1'     => "",
            'textField2'     => "",
            'textField3'     => "",
            'textField4'     => "",
            'textField5'     => "",
            'textField6'     => "",
            'textField7'     => "",
            'textField8'     => "",
            'textField9'     => "",
            'textField10'    => "",
            'varcharField1'  => "",
            'varcharField2'  => "",
            'varcharField3'  => "",
            'varcharField4'  => "",
            'varcharField5'  => "",
            'varcharField6'  => "",
            'varcharField7'  => "",
            'varcharField8'  => "",
            'varcharField9'  => "",
            'varcharField10' => ""
        ));

        if (empty($fields)) {
            return $this->getProfileDao()->getProfile($id);
        }

        if (isset($fields['smallAvatar']) && isset($fields['mediumAvatar']) && isset($fields['largeAvatar'])) {
            $this->getUserDao()->updateUser($id, array(
                'smallAvatar'  => $fields['smallAvatar'],
                'mediumAvatar' => $fields['mediumAvatar'],
                'largeAvatar'  => $fields['largeAvatar']
            ));
        }

        unset($fields['smallAvatar']);
        unset($fields['mediumAvatar']);
        unset($fields['largeAvatar']);

        if (isset($fields['title'])) {
            $this->getUserDao()->updateUser($id, array('title' => $fields['title']));
        }

        unset($fields['title']);

        if (isset($fields['staffNo'])) {
            $this->updateUserStaffNo($fields['staffNo'], $id);
        }

        unset($fields['staffNo']);

        if (isset($fields['organizationId'])) {
            $this->getUserDao()->updateUser($id, array('organizationId' => $fields['organizationId']));
        }

        unset($fields['organizationId']);

        if (!empty($fields['gender']) && !in_array($fields['gender'], array('male', 'female', 'secret'))) {
            throw $this->createServiceException('性别不正确，更新用户失败。');
        }

        if (!empty($fields['birthday']) && !SimpleValidator::date($fields['birthday'])) {
            throw $this->createServiceException('生日不正确，更新用户失败。');
        }

        if (!empty($fields['mobile']) && !SimpleValidator::mobile($fields['mobile'])) {
            throw $this->createServiceException('手机不正确，更新用户失败。');
        }

        if (!empty($fields['qq']) && !SimpleValidator::qq($fields['qq'])) {
            throw $this->createServiceException('QQ不正确，更新用户失败。');
        }

        if (!empty($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about']);
        }

        return $this->getProfileDao()->updateProfile($id, $fields);
    }

    public function searchUsers(array $conditions, array $orderBy, $start, $limit)
    {
        $this->prepareSearchConditions($conditions);

        return parent::searchUsers($conditions, $orderBy, $start, $limit);
    }

    public function searchUserCount(array $conditions)
    {
        $this->prepareSearchConditions($conditions);

        return parent::searchUserCount($conditions);
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

        $user['email']          = $registration['email'];
        $user['emailVerified']  = isset($registration['emailVerified']) ? $registration['emailVerified'] : 0;
        $user['nickname']       = $registration['nickname'];
        $user['roles']          = array('ROLE_USER');
        $user['type']           = isset($registration['type']) ? $registration['type'] : $type;
        $user['createdIp']      = empty($registration['createdIp']) ? '' : $registration['createdIp'];
        $user['createdTime']    = time();
        $user['staffNo']        = empty($registration['staffNo']) ? '' : $registration['staffNo'];
        $user['organizationId'] = empty($registration['organizationId']) ? 0 : $registration['organizationId'];

        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());

        if (in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt']     = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup']    = 1;
        } elseif (in_array($type, array('qq', 'weibo', 'renren', 'weixinweb')) && isset($thirdLoginInfo["{$type}_set_fill_account"]) && $thirdLoginInfo["{$type}_set_fill_account"]) {
            $user['salt']     = '';
            $user['password'] = '';
            $user['setup']    = 1;
        } else {
            $user['salt']     = '';
            $user['password'] = '';
            $user['setup']    = 0;
        }

        $user = $this->unserialize(
            $this->getUserDao()->addUser($this->serialize($user))
        );

        if (isset($registration['mobile']) && "" != $registration['mobile'] && !SimpleValidator::mobile($registration['mobile'])) {
            throw $this->createServiceException('mobile error!');
        }

        if (isset($registration['idcard']) && "" != $registration['idcard'] && !SimpleValidator::idcard($registration['idcard'])) {
            throw $this->createServiceException('idcard error!');
        }

        if (isset($registration['truename']) && "" != $registration['truename'] && !SimpleValidator::truename($registration['truename'])) {
            throw $this->createServiceException('truename error!');
        }

        $profile             = array();
        $profile['id']       = $user['id'];
        $profile['mobile']   = empty($registration['mobile']) ? '' : $registration['mobile'];
        $profile['idcard']   = empty($registration['idcard']) ? '' : $registration['idcard'];
        $profile['truename'] = empty($registration['truename']) ? '' : $registration['truename'];
        $profile['company']  = empty($registration['company']) ? '' : $registration['company'];
        $profile['job']      = empty($registration['job']) ? '' : $registration['job'];
        $profile['weixin']   = empty($registration['weixin']) ? '' : $registration['weixin'];
        $profile['weibo']    = empty($registration['weibo']) ? '' : $registration['weibo'];
        $profile['qq']       = empty($registration['qq']) ? '' : $registration['qq'];
        $profile['site']     = empty($registration['site']) ? '' : $registration['site'];
        $profile['gender']   = empty($registration['gender']) ? 'secret' : $registration['gender'];

        for ($i = 1; $i <= 5; $i++) {
            $profile['intField'.$i]   = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i]  = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }

        for ($i = 1; $i <= 10; $i++) {
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? "" : $registration['varcharField'.$i];
            $profile['textField'.$i]    = empty($registration['textField'.$i]) ? "" : $registration['textField'.$i];
        }

        $this->getProfileDao()->addProfile($profile);

        if ('default' != $type) {
            $this->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
        }

        $this->getDispatcher()->dispatch('user.service.registered', new ServiceEvent($user));

        return $user;
    }

    public function getUserByLoginField($keyword)
    {
        $function = $this->matchKeyword($keyword);

        if (empty($function)) {
            return null;
        }

        $user = $this->$function($keyword);

        return $user;
    }

    /**
     * @param  $keyword                         用户登录时输入的关键字
     * @return 返回查询用户的函数名
     */
    private function matchKeyword($keyword)
    {
        if (SimpleValidator::email($keyword)) {
            return 'getUserByEmail';
        }

        if (SimpleValidator::staffNo($keyword)) {
            if (strlen($keyword) != 11) {
                return 'getUserByStaffNo';
            }

            if (SimpleValidator::mobile($keyword)) {
                return 'getUserByVerifiedMobile';
            }

            return 'getUserByStaffNo';
        }

        return 'getUserByNickname';
    }

    private function serialize(array $user)
    {
        $user['roles'] = empty($user['roles']) ? '' : '|'.implode('|', $user['roles']).'|';
        return $user;
    }

    private function unserialize(array $user = null)
    {
        if (empty($user)) {
            return null;
        }

        $user['roles'] = empty($user['roles']) ? array() : explode('|', trim($user['roles'], '|'));
        return $user;
    }

    private function unserializes(array $users)
    {
        return array_map(function ($user) {
            return $this->unserialize($user);
        }, $users);
    }

    protected function getOrganizationService()
    {
        return $this->createService("Mooc:Organization.OrganizationService");
    }

    private function prepareSearchConditions(&$conditions)
    {
        if (!empty($conditions['includeChildren']) && isset($conditions['organizationId'])) {
            if (!empty($conditions['organizationId'])) {
                $childrenIds                   = $this->getOrganizationService()->findOrganizationChildrenIds($conditions['organizationId']);
                $conditions['organizationIds'] = array_merge(array($conditions['organizationId']), $childrenIds);
            }

            unset($conditions['organizationId']);
            unset($conditions['includeChildren']);
        }
    }
}
