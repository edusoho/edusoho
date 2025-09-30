<?php

namespace Biz\User\Service\Impl;

use AppBundle\Common\SimpleValidator;
use AppBundle\Common\TimeMachine;
use Biz\BaseService;
use Biz\Sensitive\SensitiveException;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\RateLimiter\RateLimiter;
use Topxia\Service\Common\ServiceKernel;

class AuthServiceImpl extends BaseService implements AuthService
{
    const MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 30;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY = 10;

    const HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR = 1;

    private $partner = null;

    public function register($registration, $type = 'default')
    {
        if (isset($registration['nickname']) && !empty($registration['nickname'])
            && $this->getSensitiveService()->scanText($registration['nickname'])) {
            $this->createNewException(SensitiveException::FORBIDDEN_WORDS());
        }

        //营销平台不需要注册频率限制
        if (!$this->isMarketingType($registration) && $this->registerLimitValidator($registration)) {
            $this->createNewException(UserException::FORBIDDEN_REGISTER_LIMIT());
        }

        //FIXME 应该调用GeneralDaoImpl里的事务
        $this->getKernel()->getConnection()->beginTransaction();
        try {
            $registration = $this->refillFormData($registration, $type);
            $registration['providerType'] = 'default';
            $newUser = $this->getUserService()->register(
                $registration,
                $this->biz['user.register.type.toolkit']->getRegisterTypes($registration)
            );

            $this->getKernel()->getConnection()->commit();

            return $newUser;
        } catch (\Exception $e) {
            $this->getKernel()->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function registerLimitValidator($registration)
    {
        $authSettings = $this->getSettingService()->get('auth', []);
        $user = $this->getCurrentUser();

        if (!$user->isAdmin() && isset($authSettings['register_protective'])) {
            $status = $this->protectiveRule($authSettings['register_protective'], $registration['createdIp']);

            if (!$status) {
                return true;
            }
        }

        return false;
    }

    protected function protectiveRule($type, $ip)
    {
        switch ($type) {
            case 'middle':
                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $limiter */
                $limiter = $factory('register.ip.mid_one_day', self::MID_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);

                $remain = $limiter->check($ip);

                if (0 == $remain) {
                    return false;
                }

                return true;
            case 'high':
                $factory = $this->biz['ratelimiter.factory'];
                /** @var RateLimiter $dayLimiter */
                $dayLimiter = $factory('register.ip.high_one_day', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_DAY, TimeMachine::ONE_DAY);
                $remain = $dayLimiter->check($ip);
                if (0 == $remain) {
                    return false;
                }

                /** @var RateLimiter $hourLimiter */
                $hourLimiter = $factory('register.ip.high_one_hour', self::HIGH_IP_MAX_ALLOW_ATTEMPT_ONE_HOUR, TimeMachine::ONE_HOUR);
                $remain = $hourLimiter->check($ip);
                if (0 == $remain) {
                    return false;
                }

                return true;
            default:
                return true;
        }
    }

    protected function refillFormData($registration, $type = 'default')
    {
        if ('default' == $type) {
            $registration = $this->getUserService()->parseRegistration($registration);
        }

        if (!isset($registration['nickname']) || empty($registration['nickname'])) {
            $registration['nickname'] = $this->getUserService()->generateNickname($registration);
        }

        if ($this->getUserService()->isMobileRegisterMode() && !isset($registration['email'])) {
            $registration['email'] = $this->getUserService()->generateEmail($registration);
        }

        if ('marketing' === $type && !isset($registration['email'])) {
            $registration['email'] = $this->getUserService()->generateEmail($registration);
        }
        $registration = $this->fillOrgId($registration);

        return $registration;
    }

    public function changeNickname($userId, $newName)
    {
        $this->getUserService()->changeNickname($userId, $newName);
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        $this->getUserService()->changeEmail($userId, $newEmail);
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $this->getUserService()->changePassword($userId, $newPassword);
    }

    public function changePayPassword($userId, $userLoginPassword, $newPayPassword)
    {
        if (!$this->checkPassword($userId, $userLoginPassword)) {
            $this->createNewException(UserException::PASSWORD_ERROR());
        }

        $this->getUserService()->changePayPassword($userId, $newPayPassword);
    }

    public function changePayPasswordWithoutLoginPassword($userId, $newPayPassword)
    {
        $this->getUserService()->changePayPassword($userId, $newPayPassword);
    }

    public function checkUsername($username, $randomName = '')
    {
        //如果一步注册则$randomName为空，正常校验discus和系统校验，如果两步注册，则判断是否使用默认生成的，如果是，跳过discus和系统校验
        if (empty($randomName) || $username != $randomName) {
            if (!SimpleValidator::nickname($username)) {
                return ['error_mismatching', '用户名不合法!'];
            }

            $avaliable = $this->getUserService()->isNicknameAvaliable($username);

            if (!$avaliable) {
                return ['error_duplicate', '名称已被占用，请更换其他用户名'];
            }
        }

        return ['success', ''];
    }

    public function checkEmail($email)
    {
        $avaliable = $this->getUserService()->isEmailAvaliable($email);

        if (!$avaliable) {
            return ['error_duplicate', 'Email已存在!'];
        }

        return ['success', ''];
    }

    public function checkMobile($mobile)
    {
        $avaliable = $this->getUserService()->isMobileAvaliable($mobile);

        if (!$avaliable) {
            return ['error_duplicate', '手机号已被绑定，请更换其他手机号'];
        }

        return ['success', ''];
    }

    public function checkEmailOrMobile($emailOrMobile)
    {
        if (SimpleValidator::email($emailOrMobile)) {
            return $this->checkEmail($emailOrMobile);
        } elseif (SimpleValidator::mobile($emailOrMobile)) {
            return $this->checkMobile($emailOrMobile);
        } else {
            return ['error_dateInput', '电子邮箱或者手机号码格式不正确!'];
        }
    }

    public function checkPassword($userId, $password)
    {
        return $this->getUserService()->verifyPassword($userId, $password);
    }

    public function checkPayPassword($userId, $payPassword)
    {
        return $this->getUserService()->verifyPayPassword($userId, $payPassword);
    }

    public function hasPartnerAuth()
    {
        return false;
    }

    public function isRegisterEnabled()
    {
        $auth = $this->getSettingService()->get('auth');
        if ($auth && (isset($auth['register_enabled']) && 'closed' === $auth['register_enabled'])) {
            return false;
        }
        if ($auth && array_key_exists('register_mode', $auth)) {
            return in_array($auth['register_mode'], ['email', 'mobile', 'email_or_mobile']);
        }

        return true;
    }

    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    private function isMarketingType($registration)
    {
        return isset($registration['type']) && 'marketing' == $registration['type'];
    }
}
