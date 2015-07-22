<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Endroid\QrCode\QrCode;


class CommonController extends BaseController
{

    public function qrcodeAction(Request $request)
    {
        $text = $request->get('text');

        $qrCode = new QrCode();
        $qrCode->setText($text);
        $qrCode->setSize(250);
        $qrCode->setPadding(10);
        $img = $qrCode->get('png');

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="qrcode.png"'
        );
        return new Response($img, 200, $headers);
    }

    public function generateLearnQrcodeAction()
     {   
        
        // $originUrl = "http://www.baidu.com";
        // $user = $this->getUserService()->getCurrentUser();
        // if (!$user->isLogin()){
        //     // $url = $request->get('url');
        //     $url = $originUrl;
        // }else {
        //     $route_type = $request->get('page_type');
        //     if ($route_type = 'classroom'){
        //         $token = $this->getTokenService()->makeToken('qrcode.classroom',array('userId'=>$user['id'],'data' => 3, 'times' => 0, 'duration' => 3600));
        //         $url = $originUrl.'/'.$token['token'];        
        //     }elseif ($route_type = 'course') {
        //         $token = $this->getTokenService()->makeToken('qrcode.classroom',array('userId'=>$user['id'],'data' => 3, 'times' => 0, 'duration' => 3600));
        //         $url = $originUrl.'/'.$token['token'];
        //     }else {
        //         $token = $this->getTokenService()->makeToken('qrcode.classroom',array('userId'=>$user['id'],'data' => 3, 'times' => 0, 'duration' => 3600));
        //         $url = $originUrl.'/'.$token['token'];
        //     }
           
        // }
        // $qrCode = new QrCode();
        // $qrCode->setText($url);
        // $qrCode->setSize(250);
        // $qrCode->setPadding(10);
        // $img = $qrCode->get('png');
        // $headers = array(
        //     'Content-Type' => 'image/png',
        //     'Content-Disposition' => 'inline; filename="qrcode.png"'
        // );
        // return new Response($);
        // $h = $this->createUser();
        // // $useer = array();
        // $useer = array();
        // $useer['id'] = $h['id'];
        // $useer['email'] = "uswdaer@usdaer.com";
        // // $useer['nickname'] = "uswwqere";
        // // $user['password'] = "user";
        // $useer['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        // $this->authenticateUser($useer);
        // $result = $this->getUserService()->getCurrentUser();
        // print_r($result);
        // return new Response("hello world");

    }

    public function crontabAction(Request $request)
    {
        $this->getServiceKernel()->createService('Crontab.CrontabService')->scheduleJobs();
        return $this->createJsonResponse(true);
    }
    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }
    private function createUser()
    {
        $user = array();
        $user['email'] = "uswdaer@usdaer.com";
        $user['nickname'] = "uswwqere";
        $user['password'] = "user";
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');

        return $this->getUserService()->register($user);
    }

}