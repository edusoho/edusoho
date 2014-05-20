<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class GlobalController extends MobileController
{
    public function __construct()
    {
        $this->setResultStatus();
    }
    
    public function getCarouselAction(Request $request)
    {
        $carousel = array(
            array(
                "image"=>MobileController::$webHost."/mobile/carousel/1.jpg",
                "action"=>"none",
                "title"=>""
            ),
            array(
                "image"=>MobileController::$webHost."/mobile/carousel/2.jpg",
                "action"=>"none",
                "title"=>""
            ),
            array(
                "image"=>MobileController::$webHost."/mobile/carousel/3.jpg",
                "action"=>"none",
                "title"=>""
            ),
        );
        return $this->createJson($request, $carousel);
    }

    public function verifySchoolAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());
        if($site) {
            $this->setResultStatus("success");
            $result['school'] = array(
                "name"=>$site['name'],
                "slogan"=>$site['slogan'],
                "url"=>$site['url'],
                "logo"=>$site['logo']
                );
        }
        return $this->createJson($request, $this->result);
    }

    public function getRecommendSchoolAction(Request $request)
    {
        $schools = array(
            array(
                "title"=>"开源力量",
                "info"=>"开源力量-网校简介",
                "logo"=>"http://192.168.12.7/files/mobile/school/nxw.png",
                "url"=>"http://192.168.12.7"
            ),
            array(
                "title"=>"小象科技",
                "info"=>"小象科技-网校简介",
                "logo"=>"http://192.168.12.7/files/mobile/school/nxw.png",
                "url"=>"http://192.168.12.7"
            ),
            array(
                "title"=>"好知网",
                "info"=>"好知网-网校简介",
                "logo"=>"http://192.168.12.7/files/mobile/school/nxw.png",
                "url"=>"http://192.168.12.7"
            ),
            array(
                "title"=>"年兄网",
                "info"=>"年兄网-网校简介",
                "logo"=>"http://192.168.12.7/files/mobile/school/nxw.png",
                "url"=>"http://192.168.12.7"
            )
        );
        return $this->createJson($request, $schools);
    }

    public function getSchoolAboutAction(Request $request)
    {
        $result = array(
            "info"=>"这是网校简介"
        );
        return $this->createJson($request, $result);
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
