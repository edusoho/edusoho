<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Sms\SmsException;
use Biz\User\UserException;

class UserPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function update(ApiRequest $request, $identify, $type)
    {
        $function = 'resetPasswordBy'.ucfirst($type);

        return \call_user_func([$this, $function], $identify, $request);
    }

    private function resetPasswordByMobile($mobile, $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, [
            'smsToken',
            'smsCode',
            'encrypt_password',
        ])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }

        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($fields['encrypt_password']), $request->getHttpRequest()->getHost());
        if (!SimpleValidator::highPassword($password)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $result = $this->getBizSms()->check(BizSms::SMS_FORGET_PASSWORD, $mobile, $fields['smsToken'], $fields['smsCode']);

        if (BizSms::STATUS_SUCCESS != $result) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        $this->getUserService()->changePassword($user['id'], $password);
        $this->getTokenService()->destoryToken($fields['smsToken']);

        return $user;
    }

    private function resetPasswordByEmail($email, $request)
    {
        $biz = $this->getBiz();
        $dragCaptcha = $biz['biz_drag_captcha'];
        $dragCaptcha->check($request->request->get('dragCaptchaToken'));

        $user = $this->getUserService()->getUserByEmail($email);
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
        try {
            $site = $this->getSettingService()->get('site', []);
            $mailOptions = [
                'to' => $user['email'],
                'template' => 'email_reset_password',
                'format' => 'html',
                'params' => [
                    'nickname' => $user['nickname'],
                    'verifyurl' => $this->getHttpHost().'/password/reset/update?token='.$token,
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ],
            ];

            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'password-reset', '重设密码邮件发送失败:'.$e->getMessage());
            throw $e;
        }
        $this->getLogService()->info('user', 'password-reset', "{$user['email']}向发送了找回密码邮件。");

        return $user;
    }

    protected function getHttpHost()
    {
        return $this->getSchema()."://{$_SERVER['HTTP_HOST']}";
    }

    protected function getSchema()
    {
        $https = empty($_SERVER['HTTPS']) ? '' : $_SERVER['HTTPS'];
        if (!empty($https) && 'off' !== strtolower($https)) {
            return 'https';
        }

        return 'http';
    }

    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
