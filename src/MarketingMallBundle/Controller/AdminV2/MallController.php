<?php

namespace MarketingMallBundle\Controller\AdminV2;

use AppBundle\Common\SmsToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Firebase\JWT\JWT;
use MarketingMallBundle\Client\MarketingMallApi;
use Symfony\Component\HttpFoundation\Request;

class MallController extends BaseController
{
    public function indexAction(Request $request)
    {
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        if (empty($mallSettings)) {
            $mallSettings = $this->initSchool();
        }

        $authorization = JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'userInfo' => $this->getUserInfo(), 'access_key' => $mallSettings['access_key'], 'header' => 'MARKETING_MALL'], $mallSettings['secret_key']);
        $mallUrl = $this->getSchema() . $this->container->getParameter('marketing_mall_url') . '/console-pc/';

        $url = $mallUrl . '?token=' . $authorization . '&code=' . $mallSettings['access_key'] . '&url=' . $this->getSchema() . $_SERVER['HTTP_HOST'];

        return $this->render('MarketingMallBundle:admin-v2/mall:index.html.twig', [
            'url' => $url,
            'options' => [
                'overview' => [
                    'isSmsConfigured' => $this->isSmsConfigured(),
                    'isWechatMobileConfigured' => $this->isWechatMobileConfigured(),
                ],
                'dealSetting' => [

                ],
            ],
        ]);
    }

    private function isSmsConfigured()
    {
        return true;
    }

    private function isWechatMobileConfigured()
    {
        return true;
    }

    public function mobileBindAction(Request $request)
    {

        $targetUrl = $this->getTargetPath($request) ?: $this->generateUrl('homepage');
        return $this->render('MarketingMallBundle:admin-v2/mall:mobile.html.twig', ['targetUrl' => $targetUrl]);
    }

    public function introduceAction(Request $request)
    {
        return $this->render('MarketingMallBundle:admin-v2/mall:introduce.html.twig', []);
    }

    protected function initSchool()
    {
        $storages = $this->getSettingService()->get('storage', []);

        $client = new MarketingMallApi($storages);
        $authorization = JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'userInfo' => $this->getUserInfo(), 'access_key' => $storages['cloud_access_key'], 'header' => 'MARKETING_MALL'], $storages['cloud_secret_key']);
        $result = $client->init([
            'token' => $authorization,
            'url' => $this->getSchema() . $_SERVER['HTTP_HOST'],
            'code' => $storages['cloud_access_key'],
        ]);
        $setting = [
            'access_key' => $result['accessKey'],
            'secret_key' => $result['secretKey'],
            'code' => $result['code'],
        ];
        $this->getSettingService()->set('marketing_mall', $setting);

        return $setting;
    }

    private function getUserInfo()
    {
        $user = $this->getUserService()->getUserAndProfile($this->getCurrentUser()->getId());

        return [
            'nickname' => $user['nickname'],
            'truename' => $user['truename'],
            'avatar' => $this->getWebExtension()->getFurl($user['smallAvatar'], 'avatar.png'),
            'mobile' => $user['verifiedMobile'],
            'email' => $user['email'],
        ];
    }

    protected function getSchema()
    {
        $https = empty($_SERVER['HTTPS']) ? '' : $_SERVER['HTTPS'];
        if (!empty($https) && 'off' !== strtolower($https)) {
            return 'https://';
        }

        return 'http://';
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}