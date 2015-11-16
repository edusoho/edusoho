<?php
namespace Mooc\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MobileController extends BaseController
{
    public function indexAction(Request $request)
    {
        $mobile = $this->setting('mobile', array());

        if (empty($mobile['enabled'])) {
            return $this->createMessageResponse('info', '客户端尚未开启！');
        }

        $result = CloudAPIFactory::create('leaf')->get('/me');

        if (array_key_exists('ver', $mobile) && $mobile['ver']) {
            $mobileCode = ((array_key_exists("mobileCode", $result) && !empty($result["mobileCode"])) ? $result["mobileCode"] : "edusoho-mooc");
        } else {
            $mobileCode = ((array_key_exists("mobileCode", $result) && !empty($result["mobileCode"])) ? $result["mobileCode"] : "edusoho-mooc");
        }

        return $this->render('MoocWebBundle:Mobile:index.html.twig', array(
            'host'       => $request->getHttpHost(),
            'mobileCode' => $mobileCode,
            'mobile'     => $mobile
        ));
    }
}
