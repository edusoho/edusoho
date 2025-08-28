<?php

namespace ApiBundle\Api\Resource\EmailVerifyCode;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\SimpleValidator;
use AppBundle\Common\TimeMachine;
use Biz\Common\CommonException;
use Biz\System\SettingException;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class EmailVerifyCode extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $this->biz['biz_drag_captcha']->check($request->request->get('dragCaptchaToken'));
        $cloudMailSwitch = $this->getSettingService()->get('cloud_email_crm', []);
        $mailer = $this->getSettingService()->get('mailer', []);
        $mailEnable = (isset($cloudMailSwitch['status']) && 'enable' === $cloudMailSwitch['status']) || (isset($mailer['enabled']) && $mailer['enabled']);
        if (empty($mailEnable)) {
            throw SettingException::MAIL_DISABLE();
        }
        $email = $request->request->get('email');
        if (!SimpleValidator::email($email)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $user = $this->getUserService()->getUserByEmail($email);
        if ($user) {
            throw UserException::EMAIL_EXISTED();
        }
        $this->biz['email_rate_limiter']->handle($request->getHttpRequest());
        $verifyCode = $this->generateVerifyCode();
        $mailOptions = [
            'to' => $email,
            'template' => 'email_verify_code',
            'params' => [
                'verifyCode' => $verifyCode,
            ],
        ];
        $mail = $this->biz['mail_factory']($mailOptions);
        $mail->send();

        $token = $this->getTokenService()->makeToken('email_verify_code', [
            'times' => 10,
            'duration' => TimeMachine::HALF_HOUR,
            'userId' => 0,
            'data' => [
                'code' => $verifyCode,
                'email' => $email,
            ],
        ]);

        return ['emailToken' => $token['token']];
    }

    private function generateVerifyCode($length = 6)
    {
        $code = rand(0, 9);

        for ($i = 1; $i < $length; ++$i) {
            $code = $code . rand(0, 9);
        }

        return $code;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
