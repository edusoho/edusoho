<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

abstract class BaseRegister
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function register($registration, $type)
    {
        $this->validate($registration, $type);

        $user = $this->createUser($registration, $type);
        $this->createUserProfile($registration, $user);

        $this->afterSave($registration, $type, $user);

        return array($user, $this->createPerInviteUser($registration, $user));
    }

    /**
     * 用于user_profile表
     * key 为 $registration 内的属性名
     * value = 默认值（$registration内无属性，使用默认值)
     */
    protected function getCreatedProfileFields()
    {
        return array(
            'mobile' => '',
            'idcard' => '',
            'truename' => '',
            'company' => '',
            'job' => '',
            'weixin' => '',
            'weibo' => '',
            'qq' => '',
            'site' => '',
            'gender' => 'secret',
        );
    }

    /**
     * 用于user表
     * key 为 $registration 内的属性名
     * value = 默认值（$registration内无属性，使用默认值)
     */
    protected function getCreatedUserFields()
    {
        return array(
            'verifiedMobile' => '',
            'email' => '',
            'emailVerified' => 0,
            'nickname' => '',
            'createdIp' => '',
            'guid' => null,
            'registeredWay' => '',
        );
    }

    protected function validate($registration, $type)
    {
        if (!SimpleValidator::nickname($registration['nickname'])) {
            throw new InvalidArgumentException('Invalid nickname');
        }

        if (!$this->getUserService()->isNicknameAvaliable($registration['nickname'])) {
            throw new InvalidArgumentException('Nickname Occupied');
        }

        if (!empty($registration['idcard']) && !SimpleValidator::idcard($registration['idcard'])) {
            throw new InvalidArgumentException('Invalid ID number');
        }

        if (!empty($registration['truename']) && !SimpleValidator::truename($registration['truename'])) {
            throw new InvalidArgumentException('Invalid truename');
        }
    }

    protected function beforeSave($registration, $type, $user = array())
    {
        foreach ($this->getCreatedUserFields() as $attr => $defaultValue) {
            if (!empty($registration[$attr])) {
                $user[$attr] = $registration[$attr];
            } elseif (isset($defaultValue)) {
                $user[$attr] = $defaultValue;
            }
        }

        $user['roles'] = array('ROLE_USER');
        $user['type'] = isset($registration['type']) ? $registration['type'] : $type;
        $user['createdTime'] = time();

        if (in_array($type, array('default', 'phpwind', 'discuz'))) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 1;
        } elseif ('marketing' === $type) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
            $user['setup'] = 0;
        } else {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 0;
        }

        if (isset($registration['orgId'])) {
            $user['orgId'] = $registration['orgId'];
            $user['orgCode'] = $registration['orgCode'];
        }

        return $user;
    }

    protected function afterSave($registration, $type, $user)
    {
    }

    /**
     * return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * return \Biz\User\Dao\UserDao
     */
    protected function getUserDao()
    {
        return $this->biz->dao('User:UserDao');
    }

    /**
     * return \Biz\User\Dao\UserProfileDao
     */
    protected function getProfileDao()
    {
        return $this->biz->dao('User:UserProfileDao');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    private function createUser($registration, $type)
    {
        $user = $this->beforeSave($registration, $type);

        return $this->getUserDao()->create($user);
    }

    private function createUserProfile($registration, $user)
    {
        $profile = array();
        $profile['id'] = $user['id'];

        foreach ($this->getCreatedProfileFields() as $attr => $defaultValue) {
            if (!empty($registration[$attr])) {
                $profile[$attr] = $registration[$attr];
            } elseif (isset($defaultValue)) {
                $profile[$attr] = $defaultValue;
            }
        }

        for ($i = 1; $i <= 5; ++$i) {
            $profile['intField'.$i] = empty($registration['intField'.$i]) ? null : $registration['intField'.$i];
            $profile['dateField'.$i] = empty($registration['dateField'.$i]) ? null : $registration['dateField'.$i];
            $profile['floatField'.$i] = empty($registration['floatField'.$i]) ? null : $registration['floatField'.$i];
        }

        for ($i = 1; $i <= 10; ++$i) {
            $profile['varcharField'.$i] = empty($registration['varcharField'.$i]) ? '' : $registration['varcharField'.$i];
            $profile['textField'.$i] = empty($registration['textField'.$i]) ? '' : $registration['textField'.$i];
        }

        $this->getProfileDao()->create($profile);
    }

    private function createPerInviteUser($registration, $user)
    {
        $inviteUser = null;
        if (!empty($registration['invite_code'])) {
            $inviteUser = $this->getUserDao()->getByInviteCode($registration['invite_code']);
        }

        if (!empty($inviteUser)) {
            $this->getInviteRecordService()->createInviteRecord($inviteUser['id'], $user['id']);
            $invitedCoupon = $this->getCouponService()->generateInviteCoupon($user['id'], 'register');

            if (!empty($invitedCoupon)) {
                $card = $this->getCardService()->getCardByCardId($invitedCoupon['id']);
                $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($user['id'], array('invitedUserCardId' => $card['cardId']));
            }
        }

        return $inviteUser;
    }
}
