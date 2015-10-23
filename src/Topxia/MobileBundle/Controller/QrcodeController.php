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
            $url = $request->getSchemeAndHttpHost() . '/mapi_v2/User/loginWithToken?token='.$token;
            //$url = $this->generateUrl('mapi_user_login_with_token', array('token' => $token), true);
        } else {
            //$url = $this->generateUrl('mapi_user_login_with_site', array(), true);      
            $url = $request->getSchemeAndHttpHost() . '/mapi_v2/School/loginSchoolWithSite?v=1';
        }

        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(215);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');


        $headers = array('Content-Type'     => 'image/png',
                         'Content-Disposition' => 'inline; filename="image.png"');
        return new Response($img, 200, $headers);

    }
}