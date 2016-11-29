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
        $user = $this->getCurrentUser();
        $data = $request->query->all();

        if (!$this->getUserService()->getUserByEmail($data['email'])) {
            return $this->error('5003', '该邮箱未在网校注册');
        }

        $salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $data['rawPassword'] = array(
            'salt'     => $salt,
            'password' => $this->getPasswordEncoder()->encodePassword($data['password'], $salt)
        );

        $site  = $this->getSettingService()->get('site', array());

        // try {
            $mailOptions = array(
                'to'       => $data['email'],
                'template' => 'effect_email_reset_password',
                'params'   => array(
                    'nickname'  => $user['nickname'],
                    'verifyurl' => $this->generateUrl('raw_password_update', array('token' => $data), true),
                    'sitename'  => $site['name'],
                    'siteurl'   => $site['url']
                )
            );

            $mail = MailFactory::create($mailOptions);
            $mail->send();
            $this->getLogService()->info('user', 'raw_password_update', "管理员给用户 ${user['nickname']}({$user['id']}) 发送密码重置邮件");

            return array(
                'code' => 0
            );
        // } catch (\Exception $e) {
        //     return array(
        //         'code'    => '5004',
        //         'message' => '邮箱发送失败'
        //     );   
        // }
    }
    public function filter($res)
    {
        return $res;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
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