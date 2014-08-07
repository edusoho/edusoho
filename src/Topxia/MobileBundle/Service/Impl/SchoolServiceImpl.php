<?php


namespace Topxia\MobileBundle\Service\Impl;
use Topxia\MobileBundle\Service\BaseService;
use Topxia\MobileBundle\Service\SchoolService;

class SchoolServiceImpl extends BaseService implements SchoolService {

    public function getSchoolBanner()
    {
        return $this->getSchoolBannerFromDb();
    }

    public function getSchoolSiteByQrCode()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '没有搜索到该网校！');
        }

        $token = $this->controller->getUserToken($request);
        if (empty($token) or  $token['type'] != self::TOKEN_TYPE) {
            $token = null;
        }

        if (empty($token)) {
            $user = null;
        } else {
            $user = $this->controller->getUserService()->getUser($token['userId']);
        }

        $site = $this->controller->getSettingService()->get('site', array());

        $result = array(
            'token' => empty($token) ? '' : $token['token'],
            'user' => empty($user) ? null : $this->filterUser($user),
            'site' => $this->getSiteInfo($request)
        );
        
        return $result;
    }

    public function getSchoolSite() {
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '没有搜索到该网校！');
        }

        $site = $this->controller->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($this->controller->request)
        );

        return $result;
    }

    private function getSchoolBannerFromDb()
    {
        $banner = array(
            array(
                "url"=>"",
                "action"=>"none",
                "params"=>array()
                ),
            array(
                "url"=>"",
                "action"=>"none",
                "params"=>array()
                ),
            array(
                "url"=>"",
                "action"=>"none",
                "params"=>array()
                )
        );
        return $banner;
    }

    private function getSiteInfo($request) {
        $site = $this->controller->getSettingService()->get('site', array());
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (!empty($mobile['logo'])) {
            $logo = $request->getSchemeAndHttpHost() . '/' . $mobile['logo'];
        } else {
            $logo = '';
        }
        $splashs = array();
        for ($i = 1; $i < 5; $i++) {
            if (!empty($mobile['splash' . $i])) {
                $splashs[] = $request->getSchemeAndHttpHost() . '/' . $mobile['splash' . $i];
            }
        }
        return array(
            'name' => $site['name'],
            'url' => $request->getSchemeAndHttpHost() . '/mapi_v1',
            'host'=> $request->getSchemeAndHttpHost(),
            'logo' => $logo,
            'splashs' => $splashs,
            'apiVersionRange' => array(
                "min" => "1.0.0",
                "max" => "1.0.0"
            ) ,
        );
    }
}

