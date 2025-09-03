<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Common\MathToolkit;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Distributor\Util\DistributorCookieToolkit;
use Biz\Mail\MailException;
use Biz\Sms\SmsException;
use Biz\System\Service\LogService;
use Biz\System\SettingException;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\UserException;

class User extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="public")
     */
    public function get(ApiRequest $request, $identify)
    {
        $identifyType = $request->query->get('identifyType', 'id');

        $user = null;
        switch ($identifyType) {
            case 'id':
                $user = $this->getUserService()->getUser($identify);
                break;
            case 'email':
                $user = $this->getUserService()->getUserByEmail($identify);
                break;
            case 'mobile':
                $user = $this->getUserService()->getUserByVerifiedMobile($identify);
                break;
            case 'nickname':
                $user = $this->getUserService()->getUserByNickname(urldecode($identify));
                break;
            case 'token':
                $user = $this->getUserService()->getUserByUUID($identify);
                break;
            default:
                break;
        }

        return $user;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="authenticated")
     */
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (empty($fields['encrypt_password'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $registerType = $this->validateByRegisterMode($fields);

        //校验验证码,基于token，默认10次机会
        if ('mobile' == $registerType) {
            $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $fields['mobile'], $fields['smsToken'], $fields['smsCode']);
            if (BizSms::STATUS_SUCCESS != $status) {
                throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
            }
        }
        if ('email' == $registerType && !$this->checkEmailVerifyCode($fields['email'], $fields['emailToken'], $fields['emailCode'])) {
            throw MailException::EMAIL_CODE_INVALID();
        }

        $nickname = substr(MathToolkit::uniqid(), 8, 16);
        while (!$this->getUserService()->isNicknameAvaliable($nickname)) {
            $nickname = MathToolkit::uniqid();
        }

        $user = [
            'emailOrMobile' => 'mobile' == $registerType ? $fields['mobile'] : $fields['email'],
            'nickname' => $nickname,
            'password' => $this->getPassword($fields['encrypt_password'], $request->getHttpRequest()->getHost()),
            'registeredWay' => DeviceToolkit::getMobileDeviceType($request->headers->get('user-agent')),
            'registerVisitId' => empty($fields['registerVisitId']) ? '' : $fields['registerVisitId'],
            'createdIp' => $request->getHttpRequest()->getClientIp(),
        ];
        if ('mobile' == $registerType) {
            $user['mobile'] = $fields['mobile'];
        }
        if ('email' == $registerType) {
            $user['email'] = $fields['email'];
        }

        if ($this->isPluginInstalled('Drp')) {
            $user = DistributorCookieToolkit::setCookieTokenToFields($request->getHttpRequest(), $user, DistributorCookieToolkit::USER);
        }

        $user = $this->getAuthService()->register($user);
        $user['token'] = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);
        $this->getLogService()->info('mobile', 'register', "用户{$user['nickname']}通过手机注册成功", ['userId' => $user['id']]);

        return $user;
    }

    /**
     * 更新用户信息接口,暂只更新讲师管理是否在网校显示字段,并且取消推荐
     *
     * @param $id
     *
     * @return mixed
     */
    public function update(ApiRequest $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2')) {
            throw new AccessDeniedException();
        }
        $fields = $request->request->all();
        $update = [];
        if (isset($fields['showable'])) {
            $showable = empty($fields['showable']) ? 0 : 1;
            $updateFields = ['showable' => $showable];
            if (!$showable) {
                $updateFields['promoted'] = 0;
                $updateFields['promotedSeq'] = 0;
            }
            $update = $this->getUserService()->updateUser($id, $updateFields);
        }

        return $update;
    }

    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $users = $this->getUserService()->searchUsers(['isStudent' => 1], ['id' => 'ASC'], $offset, $limit);
        $total = $this->getUserService()->countUsers(['isStudent' => 1]);

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    private function getPassword($password, $host)
    {
        return EncryptionToolkit::XXTEADecrypt(base64_decode($password), $host);
    }

    private function validateByRegisterMode($fields)
    {
        if (empty($fields['mobile']) && empty($fields['email'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $auth = $this->getSettingService()->get('auth', []);
        if (!empty($fields['mobile']) && empty($fields['email']) && 'email' == $auth['register_mode']) {
            throw SettingException::FORBIDDEN_MOBILE_REGISTER();
        }
        if (!empty($fields['email']) && empty($fields['mobile']) && 'mobile' == $auth['register_mode']) {
            throw SettingException::FORBIDDEN_EMAIL_REGISTER();
        }
        if (!empty($fields['mobile']) && !ArrayToolkit::requireds($fields, ['smsToken', 'smsCode'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        } elseif (!empty($fields['email']) && !ArrayToolkit::requireds($fields, ['emailToken', 'emailCode'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return !empty($fields['mobile']) ? 'mobile' : 'email';
    }

    private function checkEmailVerifyCode($email, $emailToken, $emailCode)
    {
        $token = $this->getTokenService()->verifyToken('email_verify_code', $emailToken);
        if (empty($token)) {
            return false;
        }
        if (0 == $token['remainedTimes']) {
            return false;
        }
        if ($token['data']['code'] !== $emailCode || $token['data']['email'] !== $email) {
            return false;
        }

        return true;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    protected function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }
}
