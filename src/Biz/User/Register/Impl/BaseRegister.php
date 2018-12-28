<?php

namespace Biz\User\Register\Impl;

use Biz\User\UserException;
use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\SimpleValidator;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

abstract class BaseRegister
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function register($registration)
    {
        if (empty($registration['type'])) {
            $registration['type'] = 'default';
        }

        $this->validate($registration);
        list($user, $registration) = $this->createUser($registration);
        $this->createUserProfile($registration, $user);
        $this->afterSave($registration, $user);

        return array($user, $this->createPerInviteUser($registration, $user['id']));
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
            'passwordInit' => 1,
            'registerVisitId' => '',
        );
    }

    protected function validate($registration)
    {
        if (!SimpleValidator::nickname($registration['nickname'])) {
            throw UserException::NICKNAME_INVALID();
        }

        if (!$this->getUserService()->isNicknameAvaliable($registration['nickname'])) {
            throw UserException::NICKNAME_EXISTED();
        }

        if (!empty($registration['idcard']) && !SimpleValidator::idcard($registration['idcard'])) {
            throw UserException::IDCARD_INVALID();
        }

        if (!empty($registration['truename']) && !SimpleValidator::truename($registration['truename'])) {
            throw UserException::TRUENAME_INVALID();
        }
    }

    protected function beforeSave($registration, $user = array())
    {
        $registration = $this->generatePartnerAuthUser($registration);

        foreach ($this->getCreatedUserFields() as $attr => $defaultValue) {
            if (isset($registration[$attr])) {
                $user[$attr] = $registration[$attr];
            } elseif (isset($defaultValue)) {
                $user[$attr] = $defaultValue;
            }
        }

        $user['roles'] = array('ROLE_USER');
        $user['type'] = $registration['type'];
        $user['createdTime'] = time();

        $type = empty($registration['providerType']) ? $registration['type'] : $registration['providerType'];
        if (in_array($type, array('default', 'phpwind', 'discuz', 'marketing'))) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
        } else {
            $user['salt'] = '';
            $user['password'] = '';
        }

        $user['setup'] = 1;

        if (isset($registration['orgId'])) {
            $user['orgId'] = $registration['orgId'];
            $user['orgCode'] = $registration['orgCode'];
        }

        $user['uuid'] = $this->getUserService()->generateUUID();

        return array($user, $registration);
    }

    protected function afterSave($registration, $user)
    {
        //　绑定 discuz　用户
        if (!empty($registration['partnerAuthUser']['id'])) {
            $this->getUserService()->bindUser(
                $this->getAuthService()->getPartnerName(),
                $registration['partnerAuthUser']['id'],
                $user['id'],
                null
            );
        }
    }

    /**
     * return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getAuthService()
    {
        return $this->biz->service('User:AuthService');
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

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->biz->service('User:InviteRecordService');
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->biz->service('Card:CardService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    private function createUser($registration)
    {
        list($user, $registration) = $this->beforeSave($registration);

        return array($this->getUserDao()->create($user), $registration);
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

    private function createPerInviteUser($registration, $userId)
    {
        $originUser = $this->biz['user'];

        $invitedCode = empty($originUser['invitedCode']) ? '' : $originUser['invitedCode'];
        $invitedCode = empty($registration['invitedCode']) ? $invitedCode : $registration['invitedCode'];
        $inviteUser = empty($invitedCode) ? array() : $this->getUserDao()->getByInviteCode($invitedCode);

        if (!empty($inviteUser)) {
            $this->getInviteRecordService()->createInviteRecord($inviteUser['id'], $userId);
            $invitedCoupon = $this->getCouponService()->generateInviteCoupon($userId, 'register');

            if (!empty($invitedCoupon)) {
                $card = $this->getCardService()->getCardByCardId($invitedCoupon['id']);
                $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($userId, array('invitedUserCardId' => $card['cardId']));
            }
        }

        return $inviteUser;
    }

    private function generatePartnerAuthUser($registration)
    {
        if ($this->getAuthService()->hasPartnerAuth()) {
            // 从discuz中注册过来
            if (!empty($registration['token']['userId'])) {
                $registration['type'] = 'discuz';
                $registration['partnerAuthUser'] = array(
                    'id' => $registration['token']['userId'],
                );
            } else { // 非discuz注册
                $provider = $this->getAuthService()->getAuthProvider();
                $registration['partnerAuthUser'] = $provider->register($registration);
            }
        }

        return $registration;
    }
}
