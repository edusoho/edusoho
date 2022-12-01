<?php

namespace MarketingMallBundle\Controller\AdminV2;

use AppBundle\Common\SmsToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\CloudAPIFactory;
use Firebase\JWT\JWT;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use Symfony\Component\HttpFoundation\Request;

class MallController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if (!$this->getMallService()->isShow()){
            throw $this->createAccessDeniedException();
        }

        if (empty($user['verifiedMobile'])) {
            return $this->redirectToRoute('admin_v2_mall_mobile_bind');
        }
        if (!$this->getMallService()->isIntroduceRead()) {
            return $this->redirectToRoute('admin_v2_mall_introduce');
        }
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        if (empty($mallSettings)) {
            $mallSettings = $this->getMallService()->init($this->getUserInfo(), $request->getSchemeAndHttpHost());
        }

        $authorization = JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'userInfo' => $this->getUserInfo(), 'access_key' => $mallSettings['access_key'], 'header' => 'MARKETING_MALL'], $mallSettings['secret_key']);
        $mallUrl = $this->getSchema() . $this->container->getParameter('marketing_mall_url') . '/console-pc/';

        $url = "{$mallUrl}?token={$authorization}&code={$mallSettings['access_key']}&url={$request->getSchemeAndHttpHost()}&schoolCode={$mallSettings['code']}";

        $options = [
            'overview' => [
                'isSmsConfigured' => $this->isSmsConfigured(),
                'hasSmsPermission' => $this->getCurrentUser()->hasPermission('admin_v2_edu_cloud_sms_setting'),
                'smsUrl' => $this->generateUrl('admin_v2_edu_cloud_sms_setting'),
                'isWechatMobileConfigured' => $this->isWechatMobileConfigured(),
                'hasWechatMobilePermission' => $this->getCurrentUser()->hasPermission('admin_v2_setting_wechat_auth'),
                'wechatMobileUrl' => $this->generateUrl('admin_v2_setting_wechat_auth'),
                'isNewMiniSchool' => $this->isNewMiniSchool(),
                'miniSchoolSettingUrl' => $this->generateUrl('admin_v2_wap_set').'#theme-page',
            ],
            'dealSetting' => [
                'isWechatMobileConfigured' => $this->isWechatMobileConfigured(),
                'hasWechatMobilePermission' => $this->getCurrentUser()->hasPermission('admin_v2_setting_wechat_auth'),
                'wechatMobileUrl' => $this->generateUrl('admin_v2_setting_wechat_auth'),
            ],
        ];

        return $this->render('MarketingMallBundle:admin-v2/mall:index.html.twig', [
            'url' => $url,
            'options' => $options,
        ]);
    }

    private function isSmsConfigured(): bool
    {
        $smsSetting = $this->setting('cloud_sms', []);
        if (empty($smsSetting['sms_enabled'])) {
            return false;
        }
        //todo 通知记录并缓存
        try {
            $smsInfo = CloudAPIFactory::create('root')->get('/me/sms_account');

            return isset($smsInfo['usedSmsSign']['status']) && 'using' == $smsInfo['usedSmsSign']['status'];
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isWechatMobileConfigured(): bool
    {
        $wechatSetting = $this->setting('payment', []);
        if (empty($wechatSetting['wxpay_appid']) || empty($wechatSetting['wxpay_secret']) || empty($wechatSetting['wxpay_mp_secret'])) {
            return false;
        }

        return true;
    }

    private function isNewMiniSchool()
    {
        $wapSetting = $this->setting('wap', []);

        return isset($wapSetting['version']) && 2 == $wapSetting['version'];
    }

    public function mobileBindAction(Request $request)
    {
        $scenario = 'sms_bind';

        if ($request->isMethod('POST')) {
            list($result, $sessionField) = SmsToolkit::smsCheck($request, $scenario);

            if ($result) {
                $this->getUserService()->changeMobile($this->getCurrentUser()->getId(), $sessionField['to']);

                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.success']);
            } else {
                return $this->createJsonResponse(['message' => 'user.settings.security.mobile_bind.fail'], 403);
            }
        }
        $user = $this->getUser();
        if (!empty($user['verifiedMobile'])) {
            return $this->redirectToRoute('admin_v2_marketing_mall');
        }

        return $this->render('MarketingMallBundle:admin-v2/mall:mobile.html.twig', ['targetUrl' => $this->generateUrl('admin_v2_marketing_mall')]);
    }

    public function introduceAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->getMallService()->readIntroduce();

            return $this->createJsonResponse(['success' => true]);
        }
        if ($this->getMallService()->isIntroduceRead()) {
            return $this->redirectToRoute('admin_v2_marketing_mall');
        }

        return $this->render('MarketingMallBundle:admin-v2/mall:introduce.html.twig', []);
    }

    private function getUserInfo()
    {
        $user = $this->getUserService()->getUserAndProfile($this->getCurrentUser()->getId());

        return [
            'id' => $user['id'],
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
        return $this->createService('System:SettingService');
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->createService('Mall:MallService');
    }
}
