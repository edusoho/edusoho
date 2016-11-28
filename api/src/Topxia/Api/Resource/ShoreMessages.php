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
        $conditions = $request->query->all();

        if (!$this->getUserService()->getUserByVerifiedMobile($conditions['mobile'])) {
            return $this->returnError('5001');
        }

        $params = array(
            'mobile'       => $conditions['mobile'],
            'category'     => 'sms_kuozhi_verify',
            'captcha_code' => $conditions['captchaCode']
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