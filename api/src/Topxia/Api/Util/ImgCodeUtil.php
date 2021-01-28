<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;

class ImgCodeUtil
{
    public function verifyImgCode($type, $imgCode, $imgToken)
    {
        $token = $this->getTokenService()->verifyToken($type, $imgToken);

        if (empty($token)) {
            throw new \Exception('图形验证码已过期');
        }
        if ($imgCode != $token['data']['img_code']) {
            throw new \Exception("图形验证码错误");
        }

        return true;
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}