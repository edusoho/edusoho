<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\BizSms;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Exception\ErrorCode;
use Biz\User\UserException;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\DeviceToolkit;
use Biz\System\SettingException;
use AppBundle\Common\SmsToolkit;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Common\CommonException; 
use AppBundle\Common\SimpleValidator;

class UserPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function update(ApiRequest $request, $identify, $type)
    {
        $biz = $this->getBiz();
        $dragCaptcha = $biz['biz_drag_captcha'];
        $dragCaptcha->check($request->request->get('dragCaptchaToken'));

        $function = 'resetPasswordBy'.ucfirst($type);
        return \call_user_func(array($this, $function),  $identify, $request);
    }

    private function resetPasswordByMobile($mobile, $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, array(
            'smsToken',
            'smsCode',
            'password',
        ))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }

        $password = $fields['password'];

        if (!SimpleValidator::password($password)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $result = $this->getBizSms()->check(BizSms::SMS_FORGET_PASSWORD, $mobile, $fields['smsToken'], $fields['smsCode']);
        
        if (BizSms::STATUS_SUCCESS != $result) {
            throw UserException::FORBIDDEN_REGISTER();
        }

        $this->getUserService()->changePassword($user['id'], $password);
        $this->getTokenService()->destoryToken($fields['smsToken']);

        return $user;
    }

    private function resetPasswordByEmail($email, $request)
    {
        $user = $this->getUserService()->getUserByEmail($email);
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }
        
        if ('discuz' == $user['type']) {
            throw UserException::FORBIDDEN_DISCUZ_USER_RESET_PASSWORD();
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
        try {
            $site = $this->getSettingService()->get('site', array());
            $mailOptions = array(
                'to' => $user['email'],
                'template' => 'email_reset_password',
                'format' => 'html',
                'params' => array(
                    'nickname' => $user['nickname'],
                    'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), true),
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ),
            );

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

    private function getSettingService()
    {
        return $this->service('System:SettingService');
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