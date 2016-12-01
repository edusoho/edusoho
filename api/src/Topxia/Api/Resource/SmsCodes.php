<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\CurlToolkit;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class SmsCodes extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $type   = $request->request->get('type');
        $mobile = $request->request->get('mobile');

        if (!in_array($type, array('sms_change_password', 'sms_verify_mobile'))) {
            return $this->error('500', '短信服务不支持该业务');
        }
        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        if ($type == 'sms_change_password') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_forget_password', $mobile);
            } catch(Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }

        if ($type == 'sms_verify_mobile') {
            try {
                $result = $this->getSmsService()->sendVerifySms('sms_bind', $mobile);
            } catch(Exception $e) {
                return $this->error('500', $e->getMessage());
            }
        }
            
        $user = $this->getCurrentUser();
        $smsToken = $this->getTokenService()->makeToken($type, array(
            'times'    => 5,
            'duration' => 60 * 2,
            'userId'   => $user['id'],
            'data'     => array(
                'sms_code' => $result['captcha_code'],
                'mobile'   => $mobile
            )
        ));

        return array(
            'mobile'   => $mobile,
            'smsToken' => $smsToken['token']
        );
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}