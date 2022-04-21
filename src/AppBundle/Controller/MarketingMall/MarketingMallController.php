<?php

namespace AppBundle\Controller\MarketingMall;

use AppBundle\Controller\BaseController;
use Biz\User\Service\TokenService;
use Firebase\JWT\JWT;
use Imagine\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class MarketingMallController extends BaseController
{
    public function tokenAction(Request $request)
    {
        $result = $this->verifyAuthorization($request);

        $this->getTokenService()->deleteTokenByTypeAndUserId('marketing_mall_access_token', 0);
        try {
            $data = $this->makeTokenData($result);
        } catch (\RuntimeException $e) {
            return $this->createJsonResponse(['code' => 500, 'message' => $e->getMessage()]);
        }

        return $this->createJsonResponse($data);
    }

    public function userinfoAction(Request $request)
    {
        $token = $request->headers->get('Authorization', 'Bearer ');
        $token = str_ireplace('Bearer ', '', $token);
        $this->getTokenService()->verifyToken('marketing_mall_access_token', $token);
        if (!$token) {
            return $this->createJsonResponse(['code' => 403, 'message' => 'token verify error']);
        }
        $data = $request->query->all();
        if (empty($data['userId'])) {
            return $this->createJsonResponse(['code' => 404, 'message' => 'Missing parameters']);
        }
        $user = $this->getUserService()->getUserAndProfile($data['userId']);

        return $this->createJsonResponse([
            'code' => 200,
            'data' => [
                'nickname' => $user['nickname'],
                'truename' => $user['truename'],
                'avatar' => $user['mediumAvatar'],
                'mobile' => $user['verifiedMobile'],
                'email' => $user['email'],
            ],
        ]);
    }

    private function verifyAuthorization($request)
    {
        $token = $request->headers->get('Authorization', 'Bearer ');
        $token = str_ireplace('Bearer ', '', $token);
        $code = $request->query->get('code', '');
        if (empty($token) || empty($code)) {
            throw new RuntimeException('lack of Authorization');
        }

        $settings = $this->getSettingService()->get('storage', []);
        try {
            $result = JWT::decode($token, md5($settings['cloud_secret_key']), ['HS256']);
        } catch (\Exception $e) {
            throw new RuntimeException('Authorization verify error.');
        }

        if (empty($result) || $result->access_key != $code || $result->access_key != $settings['cloud_access_key']) {
            throw new RuntimeException('Authorization verify error.');
        }

        return $result;
    }

    private function makeTokenData($result)
    {
        $accessToken = $this->getTokenService()->makeToken('marketing_mall_access_token', [
            'data' => $result,
            'duration' => 3600 * 24,
            'times' => 1,
        ]);
        $this->getSettingService()->set('marketing_mall_oauth', $accessToken);

        return [
            'code' => 200,
            'data' => [
                 'access_token' => $accessToken['token'],
                 'time' => time(),
             ],
        ];
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }
}
