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
use Biz\Sms\SmsException;
use Biz\System\SettingException;

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
        // 目前只支持手机注册
        $auth = $this->getSettingService()->get('auth', []);
        if (!(isset($auth['register_mode']) && in_array($auth['register_mode'], ['mobile', 'email_or_mobile']))) {
            throw SettingException::FORBIDDEN_MOBILE_REGISTER();
        }

        //校验云短信开启
        $smsSetting = $this->getSettingService()->get('cloud_sms', []);
        if (empty($smsSetting['sms_enabled'])) {
            throw SettingException::FORBIDDEN_SMS_SEND();
        }

        //校验字段缺失
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, [
            'mobile',
            'smsToken',
            'smsCode',
            'encrypt_password',
        ], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        //校验验证码,基于token，默认10次机会
        $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $fields['mobile'], $fields['smsToken'], $fields['smsCode']);
        if (BizSms::STATUS_SUCCESS != $status) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        $nickname = substr(MathToolkit::uniqid(), 8, 16);
        while (!$this->getUserService()->isNicknameAvaliable($nickname)) {
            $nickname = MathToolkit::uniqid();
        }

        $registeredWay = DeviceToolkit::getMobileDeviceType($request->headers->get('user-agent'));
        $user = [
            'mobile' => $fields['mobile'],
            'emailOrMobile' => $fields['mobile'],
            'nickname' => $nickname,
            'password' => $this->getPassword($fields['encrypt_password'], $request->getHttpRequest()->getHost()),
            'registeredWay' => $registeredWay,
            'registerVisitId' => empty($fields['registerVisitId']) ? '' : $fields['registerVisitId'],
            'createdIp' => $request->getHttpRequest()->getClientIp(),
        ];

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

    private function getPassword($password, $host)
    {
        return EncryptionToolkit::XXTEADecrypt(base64_decode($password), $host);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }

    protected function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }
}
