<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\CurlToolkit;

class ShortMessages extends BaseResource
{
    private $errorMessage = array(
        '5001' => '手机号未验证',
        ''     => ''
    );

    public function post(Application $app, Request $request)
    {
        $data = $request->query->all();

        $token = json_decode($data);

        if (!$this->getUserService()->getUserByVerifiedMobile($token['mobile'])) {
            return $this->returnError('5001');
        }

        $params = array(
            'mobile'       => $token['mobile'],
            'category'     => 'sms_kuozhi_verify',
            'captcha_code' => $token['captchaCode']
        );

        $result = CurlToolkit::request('POST', 'open.edusoho.com/sms/verify', $params);

        return $result;
    }

    public function returnError($code)
    {
        return $this->errorMessage[$code];
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}