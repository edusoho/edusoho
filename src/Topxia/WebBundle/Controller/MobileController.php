<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Endroid\QrCode\QrCode;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

class MobileController extends BaseController
{    
    protected function createAPIClient()
    {
        $settings = $this->getServiceKernel()->createService('System.SettingService')->get('storage', array());
        return new CloudAPI(array(
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        ));
    }

    public function indexAction(Request $request)
    {
        $mobile = $this->setting('mobile', array());

        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }

        $result = $this->createAPIClient()->get('/me');

        return $this->render('TopxiaWebBundle:Mobile:index.html.twig', array(
            'host' => $request->getHttpHost(),
            'mobileCode' => ( (array_key_exists("mobileCode", $result) && !empty($result["mobileCode"])) ? $result["mobileCode"] : "edusoho")
        ));
    }

    public function downloadQrcodeAction(Request $request)
    {
        $code = $request->get("code");
        $url = $this->generateUrl('mobile_download', array('from' => 'qrcode', 'code' => $code), true);     
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array('Content-Type'     => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"');
        return new Response($img, 200, $headers);
    }

    public function downloadAction(Request $request)
    {
        $params = $request->query->all();
        $baseUrl = $request->getSchemeAndHttpHost();
        return $this->redirect($baseUrl . "/mapi_v2/School/getDownloadUrl?" . http_build_query($params));
    }

}