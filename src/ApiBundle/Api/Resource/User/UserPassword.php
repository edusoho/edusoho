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

class UserPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function update(ApiRequest $request, $identify, $type)
    {
        $token = $request->query->get('token');
        $biz = $this->getBiz();
        $dragCaptcha = $biz['biz_drag_captcha'];
        $dragCaptcha->check($token);

        $function = 'resetPasswordBy'.ucfirst($type);
        return \call_user_func(array($this, $function),  $identify);
    }

    private function resetPasswordByMobile($mobile)
    {
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }
        

        //list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_forget_password');

        // if ($result) {

        //     $token = $this->getUserService()->makeToken('password-reset', $targetUser['id'], strtotime('+1 day'));
        //     $request->request->set('token', $token);

        //     return $this->redirect($this->generateUrl('password_reset_update', array(
        //         'token' => $token,
        //     )));
        // }

        // return $this->createMessageResponse('error', '手机短信验证错误，请重新找回');
    }

    private function resetPasswordByEmail($email)
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
}