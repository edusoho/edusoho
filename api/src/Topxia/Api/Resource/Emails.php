<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\SimpleValidator;
use AppBundle\Common\EncryptionToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class Emails extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $data = $request->request->all();

        if (!isset($data['email'])) {
            return $this->error('500', '请输入邮箱!');
        }

        $this->limiterCheck($data['email']);

        if (!isset($data['password'])) {
            return $this->error('500', '请输入更改后的密码!');
        }

        if (!SimpleValidator::email($data['email'])) {
            return $this->error('500', 'email不符合要求');
        }

        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($data['password']), $request->getHost());
        if (!SimpleValidator::password($password)) {
            return $this->error('500', '密码不符合要求');
        }
        $user = $this->getUserService()->getUserByEmail($data['email']);
        if (!$user) {
            return $this->error('500', '该邮箱未在网校注册');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $data['rawPassword'] = array(
            'salt' => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt),
        );

        $tokenType = 'email_password_reset';
        $EmailToken = $this->getTokenService()->makeToken($tokenType, array(
            'times' => 5,
            'duration' => 60 * 30,
            'userId' => $user['id'],
            'data' => array(
                'userId' => $user['id'],
                'rawPassword' => array(
                    'salt' => $salt,
                    'password' => $this->getPasswordEncoder()->encodePassword($password, $salt),
                ),
            ),
        ));

        $url = $this->getHttpHost().'/raw/password/update?token='.$EmailToken['token'];
        $site = $this->getSettingService()->get('site', array());

        try {
            $mailOptions = array(
                'to' => $data['email'],
                'template' => 'effect_email_reset_password',
                'params' => array(
                    'nickname' => $user['nickname'],
                    'verifyurl' => $url,
                    'sitename' => $site['name'],
                    'siteurl' => $site['url'],
                ),
            );

            $mailFactory = $this->getBiz()->offsetGet('mail_factory');
            $mail = $mailFactory($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'raw_password_update', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");

            return array(
                'code' => 0,
            );
        } catch (\Exception $e) {
            return array(
                'code' => '500',
                'message' => '邮箱发送失败',
            );
        }
    }

    public function filter($res)
    {
        return $res;
    }

    protected function limiterCheck($email)
    {
        $biz = $this->getBiz();
        $factory = $biz['ratelimiter.factory'];
        $limiter = $factory('api_password_reset', 3, 1800);
        $remain = $limiter->check($email);
        if ($remain == 0) {
            return $this->error('500', '操作过于频繁，请30分钟之后再试!');
        }

        return true;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System:LogService');
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }
}
