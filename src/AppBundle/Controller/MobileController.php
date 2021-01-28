<?php

namespace AppBundle\Controller;

use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Endroid\QrCode\QrCode;
use Biz\CloudPlatform\Client\CloudAPI;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MobileController extends BaseController
{
    protected function createAPIClient()
    {
        $settings = $this->getSettingService()->get('storage', array());

        return new CloudAPI(array(
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        ));
    }

    public function indexAction(Request $request)
    {
        $referrer = $request->headers->get('Referer');
        $isBaiduMobile = 'm.baidu.com';
        if (false !== strpos($referrer, $isBaiduMobile)) {
            return $this->redirectToRoute('homepage');
        }

        $mobile = $this->setting('mobile', array());

        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }

        $result = CloudAPIFactory::create('leaf')->get('/me');

        $mobileCode = ((array_key_exists('mobileCode', $result) && !empty($result['mobileCode'])) ? $result['mobileCode'] : 'edusohov3');

        return $this->render('mobile/index.html.twig', array(
            'host' => $request->getHttpHost(),
            'mobileCode' => $mobileCode,
            'mobileSetting' => $mobile,
        ));
    }

    public function downloadQrcodeAction(Request $request)
    {
        $code = $request->get('code');
        $url = $this->generateUrl('mobile_download', array('from' => 'qrcode', 'code' => $code), UrlGeneratorInterface::ABSOLUTE_URL);
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array('Content-Type' => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"', );

        return new Response($img, 200, $headers);
    }

    public function downloadAction(Request $request)
    {
        $params = $request->query->all();
        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->redirect($baseUrl.'/mapi_v2/School/getDownloadUrl?'.http_build_query($params));
    }

    public function usertermsAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', array());

        return $this->render('mobile/mobile-view-container.html.twig', array(
            'content' => empty($setting['user_terms_body']) ? '' : $setting['user_terms_body'],
        ));
    }

    public function privacyPolicyAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', array());

        return $this->render('mobile/mobile-view-container.html.twig', array(
            'content' => empty($setting['privacy_policy_body']) ? '' : $setting['privacy_policy_body'],
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
