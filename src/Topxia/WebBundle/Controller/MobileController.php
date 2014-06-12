<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Endroid\QrCode\QrCode;

class MobileController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Mobile:index.html.twig', array(
            'host' => $request->getHttpHost(),
        ));
    }

    public function downloadQrcodeAction(Request $request)
    {
        $url = $this->generateUrl('mobile_download', array(), true);

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(150);
        $qrCode->setPadding(10);
        $img = $qrCode->render();

        $headers = array('Content-Type'     => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"');
        return new Response($img, 200, $headers);
    }

    public function downloadAction(Request $request)
    {
        return $this->redirect('http://open.edusoho.com/mobile/download.php?from=qrcode');
    }

}