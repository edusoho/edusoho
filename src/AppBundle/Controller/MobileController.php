<?php

namespace AppBundle\Controller;

use Biz\CloudPlatform\Client\CloudAPI;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\SettingService;
use Biz\Theme\Service\ThemeService;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MobileController extends BaseController
{
    protected function createAPIClient()
    {
        $settings = $this->getSettingService()->get('storage', []);

        return new CloudAPI([
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        ]);
    }

    public function indexAction(Request $request)
    {
        $referrer = $request->headers->get('Referer');
        $isBaiduMobile = 'm.baidu.com';
        if (false !== strpos($referrer, $isBaiduMobile)) {
            return $this->redirectToRoute('homepage');
        }

        $mobile = $this->setting('mobile', []);

        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }

        $result = CloudAPIFactory::create('leaf')->get('/me');

        $mobileCode = ((array_key_exists('mobileCode', $result) && !empty($result['mobileCode'])) ? $result['mobileCode'] : 'zhixiang');

        return $this->render('mobile/index.html.twig', [
            'host' => $request->getHttpHost(),
            'mobileCode' => $mobileCode,
            'mobileSetting' => $mobile,
        ]);
    }

    public function downloadQrcodeAction(Request $request)
    {
        $code = $request->get('code');
        $url = $this->generateUrl('mobile_download', ['from' => 'qrcode', 'code' => $code], UrlGeneratorInterface::ABSOLUTE_URL);
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = ['Content-Type' => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"', ];

        return new Response($img, 200, $headers);
    }

    public function downloadMiddlePageAction(Request $request)
    {
        $mobile = $this->setting('mobile', []);
        $site = $this->setting('site', []);

        $courseId = $request->get('courseId', []);
        $goodsId = $request->get('goodsId', []);
        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }
        $result = CloudAPIFactory::create('leaf')->get('/me');
        $mobileCode = ((array_key_exists('mobileCode', $result) && !empty($result['mobileCode'])) ? $result['mobileCode'] : 'zhixiang');
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render('/mobile/download-middle-page.html.twig', [
            'mobileCode' => $mobileCode,
            'mobile' => $mobile,
            'site' => $site,
            'maincolor' => $themeConfig['config']['maincolor'],
            'courseId' => $courseId,
            'goodsId' => $goodsId,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }

    public function downloadAction(Request $request)
    {
        $params = $request->query->all();
        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->redirect($baseUrl.'/mapi_v2/School/getDownloadUrl?'.http_build_query($params));
    }

    public function usertermsAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', []);

        return $this->render('mobile/mobile-view-container.html.twig', [
            'content' => empty($setting['user_terms_body']) ? '' : $setting['user_terms_body'],
        ]);
    }

    public function privacyPolicyAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', []);

        return $this->render('mobile/mobile-view-container.html.twig', [
            'content' => empty($setting['privacy_policy_body']) ? '' : $setting['privacy_policy_body'],
        ]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->getBiz()->service('Theme:ThemeService');
    }
}
