<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Endroid\QrCode\QrCode;

class QrcodeController extends MobileController
{

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            $token = $this->createToken($user, $request);
            $url = $this->generateUrl('topxia_mobile_checkQR', array('token' => $token), true);
        } else {
            $url = $this->generateUrl('topxia_mobile_checkQR', array(), true);
        }

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(200);
        $qrCode->setPadding(10);
        $img = $qrCode->render();


        $headers = array('Content-Type'     => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"');
        return new Response($img, 200, $headers);

    }
}