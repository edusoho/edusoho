<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\MailFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class Emails extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $data = $request->query()->all();

        $token = json_decode($data);

        if (!$this->getUserService()->getUserByEmail($token['email'])) {
            return $this->error('5003', '该邮箱未在网校注册');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $token['rawPassword'] = array(
            'salt'     => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($password, $salt)
        );

        $site  = $this->setting('site', array());

        try {
            $mailOptions = array(
                'to'       => $token['email'],
                'template' => 'effect_email_reset_password',
                'params'   => array(
                    'nickname'  => $user['nickname'],
                    'verifyurl' => $this->generateUrl('raw_password_reset_update', array('token' => $token), true),
                    'sitename'  => $site['name'],
                    'siteurl'   => $site['url']
                )
            );

            $mail = MailFactory::create($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'raw_password_reset_update', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");

            return array(
                'code' => 0
            );
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'raw_password_reset_update', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件失败：".$e->getMessage());
            throw $e;
        }

        return array(
            'code'    => '5004',
            'message' => '邮箱发送失败'
        );
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}