<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Endroid\QrCode\QrCode;
use Topxia\AdminBundle\Controller\BaseController as AdminBaseController;

class MobileController extends AdminBaseController
{
    var $code = '';
    public function indexAction(Request $request)
    {
        $mobile = $this->setting('mobile', array());

        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }


        $result = $this->createAPIClient()->get('/me');
        $this->code = $result['mobileCode'];

        var_dump($result['mobileCode']);
        return $this->render('TopxiaWebBundle:Mobile:index.html.twig', array(
            'host' => $request->getHttpHost(),
           // 'mobileCode' => $result["mobileCode"] : ,"edusoho" 000000000000000000000000000000000000
        ));
    }

    public function downloadQrcodeAction(Request $request)
    {
        $url = $this->generateUrl('mobile_download', array('from' => 'qrcode', ), true);
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
        return $this->redirect($baseUrl . "/mapi_v2/School/getDownloadUrl?code={$this->code}" . http_build_query($params));
    }

}