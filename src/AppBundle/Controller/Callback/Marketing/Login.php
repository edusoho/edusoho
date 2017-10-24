<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use Codeages\Weblib\Auth\Authentication;

class Login extends MarketingBase
{
    public function authentication(Request $request)
    {
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->debug('微营销请求登录授权');
        $content = $request->getContent();
        $postData = json_decode($content, true);

        $keyProvider = new AuthKeyProvider();
        $authentication = new Authentication($keyProvider);
        try {
            $logger->debug('准备验证请求的auth');
            $authentication->auth($request);
            $logger->debug('验证请求的auth通过，请求认定为合法，处理相应逻辑');

            $mobile = $postData['mobile'];
            $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
            if (empty($user)) {
                $logger->error("根据手机：{$mobile},没有查询到用户");

                return array('code' => 'error', 'msg' => "手机号：{$mobile}，没有查询到用户");
            }
            $logger->info('ES查询到需要授权的用户，准备生成授权信息');
            $token = $this->getTokenService()->makeToken('marketing_login',
                array(
                    'data' => array(
                            'targetType' => $postData['target_type'],
                            'targetId' => $postData['target_id'],
                        ),
                    'times' => 10,
                    'duration' => 3600,
                    'userId' => $user['id'],
                ));

            return array('ticket' => $token['token']);
        } catch (\Exception $e) {
            $logger->error('ES处理微营销登录授权失败,'.$e->getMessage());

            return array('code' => 'error', 'msg' => $e->getMessage());
        }
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
