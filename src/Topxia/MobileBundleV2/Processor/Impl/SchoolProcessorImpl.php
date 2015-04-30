<?php

namespace Topxia\MobileBundleV2\Processor\Impl;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\SchoolProcessor;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class SchoolProcessorImpl extends BaseProcessor implements SchoolProcessor {

    public $banner;

    public function loginSchoolWithSite()
    {
        $version = $this->request->query->get('version', 1);
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '网校客户端未开启');
        }

        $site = $this->controller->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($this->controller->request, $version)
        );
        
        return $result;
    }

    public function getSchoolApps()
    {
        $host = $this->request->getSchemeAndHttpHost();
        $article = array(
            "code"=>"Article",
            "icon"=>$host  . "/bundles/topxiamobilebundlev2/img/article.png",
            "name"=>"网校资讯",
            "description"=>"EduSoho官方应用，网校资讯。",
            "author"=>"官方",
            "version"=>"1.0.0",
            "support_version"=>"2.4.0+",
            "action"=>array(),
            "url"=>"articleApp"
        );

        $group = array(
            "code"=>"Group",
            "icon"=>$host  . "/bundles/topxiamobilebundlev2/img/group.png",
            "name"=>"网校小组社区",
            "description"=>"网校小组社区",
            "author"=>"官方",
            "version"=>"1.0.0",
            "support_version"=>"2.4.0+",
            "action"=>array(),
            "url"=>""
        );
        return array(
            $article, $group
            );
    }
    
    public function registDevice()
    {
        $result = false;
        $parames = array();
        $parames["imei"] = $this->getParam("deviceSn",  "");
        $parames["platform"] = $this->getParam( "platform",  "");
        $parames["version"] = $this->getParam("version",  "");
        $parames["screenresolution"] = $this->getParam("screenresolution",  "");
        $parames["kernel"] = $this->getParam("kernel",  "");

        if (empty($parames["imei"]) || empty($parames["platform"])) {
            return $this->createErrorResponse("info_error", "串号或平台版本不能为空!");
        }
        if ($this->getMobileDeviceService()->addMobileDevice($parames)) {
            $result = true;
        }
        
        $this->log("regist_device", "注册客户端",  $parames);
        return true;
    }

    public function getFlashApk()
    {
        $version  = (int) $this->request->query->get("version", 9);
        if ($version > 5 && $version < 14) {
            return $this->controller->redirect("http://mobcdn.qiniudn.com/flash_2.x_3.x.apk");
        }

        if ($version > 13 && $version < 16) {
            return $this->controller->redirect("http://mobcdn.qiniudn.com/flash_4.x.apk");
        }

        return "";
    }

    public function getDownloadUrl()
    {
        $code = $this->fixHttpHeaderInject($this->request->get("code", "edusoho"));
        $client = $this->fixHttpHeaderInject($this->request->get("client", "android"));
        return $this->controller->redirect("http://www.edusoho.com/download/mobile?client={$client}&code=" . $code);
    }

    private function fixHttpHeaderInject($str)
    {
        if(empty($str)){
            return $str;
        }
        return trim(strip_tags(preg_replace('/( |\t|\r|\n|\')/', '', $str)));
    }

    public function getClientVersion()
    {
        $code = $this->getParam("code", "edusoho");
        $clientVersion = $this->sendRequest("GET", "http://www.edusoho.com/version/{$code}-android", array());
        if ("{}" == $clientVersion) {
            return null;
        }
        $clientVersion = json_decode($clientVersion); 
        $result = array(
            "show"=>"alert" == $clientVersion->updateMode ? true : false,
            "code"=>$clientVersion->versionCode,
            "androidVersion"=>$clientVersion->version,
            "updateInfo"=>$clientVersion->updateInfo,
            "updateUrl"=>$clientVersion->url
            );
        return $result;
    }

    public function suggestionLog()
    {
        $info = $this->getParam("info");
        $type = $this->getParam("type", 'bug');
        $contact = $this->getParam("contact");

        if (empty($info)) {
            return false;
        }

        $this->log("suggestion", "反馈内容", array(
            "info"=>$info,
            "type"=>$type,
            "$contact"=>$contact
            ));
        return true;
    }

    public function sendSuggestion()
    {
        $info = $this->getParam("info");
        $type = $this->getParam("type", 'bug');
        $contact = $this->getParam("contact");

        if (empty($info)) {
            return $this->createErrorResponse('error', '反馈内容不能为空！');
        }

        $this->sendRequest("POST", "http://demo.edusoho.com/mapi_v2/School/suggestionLog", array(
            "info"=>$info,
            "type"=>$type,
            "contact"=>$contact
            ));
        return true;
    }

    public function getShradCourseUrl()
    {
        $courseId = $this->request->get("courseId");
        if (empty($courseId)) {
            return new Response("课程不存在或已删除");
        }

        return $this->controller->redirect($this->controller->generateUrl('course_show', array('id' => $courseId)));
    }

    public function getUserterms()
    {
        $setting = $this->controller->getSettingService()->get('auth', array());
        $userTerms = "暂无服务条款";
        if (array_key_exists("user_terms_body", $setting)) {
            $userTerms = $setting['user_terms_body'];
        }
        
        return $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $userTerms
        ));
    }

    public function getSchoolInfo()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        
        return $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $this->controller->convertAbsoluteUrl($this->request, $mobile['about'])
        ));
    }

    public function getWeekRecommendCourses()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());

        $courseIds = explode(",", $mobile['courseIds']);
        $courses = $this->controller->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses,'id');
        $sortedCourses = array();
        foreach ( $courseIds as $value){
            if(!empty($value))
                if(array_key_exists($value, $courses)){
                    $sortedCourses[] = $courses[$value];
                }
        } 
         
        $result = array(
            "start"=>0,
            "limit"=>3,
            "data"=>$this->controller->filterCourses($sortedCourses));
        return $result;
    }

    public function getRecommendCourses()
    {
        $conditions = array(
            'status' => 'published',
            'type' => 'normal',
            "recommended"=>1
        );
        return $this->getCourseByType("recommendedSeq", $conditions);
    }

    public function getLatestCourses()
    {
        $conditions = array(
            'status' => 'published',
            'type' => 'normal'
        );
        return $this->getCourseByType("latest", $conditions);
    }

    private function getCourseByType($sort,  $conditions)
    {
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);

        $total  = $this->getCourseService()->searchCourseCount($conditions);
        $courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start,  $limit);
        $result = array(
            "start"=>$start,
            "limit"=>$limit,
            "total"=>$total,
            "data"=>$this->controller->filterCourses($courses));

        return $result;
    }

    public function getSchoolAnnouncement()
    {
        $mobile = $this->getSettingService()->get('mobile', array());
        return array(
            "info"=>$mobile['notice'],
            "action"=>"none",
            "params"=>array()
            );
    }

    public function getSchoolBanner()
    {
        $banner = array();
        $mobile = $this->getSettingService()->get('mobile', array());
        $baseUrl = $this->request->getSchemeAndHttpHost();
        for ($i=1; $i < 6; $i++) {
            $bannerIndex = $mobile["banner" . $i];
            if (!empty($bannerIndex)) { 
                    $bannerClick = $mobile["bannerClick" . $i];
                    $bannerParams = null;
                    $action = "none";
                    switch ($bannerClick) {
                        case 0:
                            $action = "none";
                            $bannerParams = null;
                            break;
                        case 1:
                            $action = "webview";
                            if(array_key_exists("bannerUrl" . $i, $mobile)){
                                $bannerParams = $mobile["bannerUrl" . $i];
                            } else {
                                $bannerParams = "";
                            }
                            break;
                        case 2:
                            $action = "course";
                            if(array_key_exists("bannerJumpToCourseId" . $i, $mobile)){
                                $bannerParams = $mobile["bannerJumpToCourseId" . $i];
                            } else {
                                $bannerParams = "";
                            }
                            break;
                    }
                    $banner[] = array(
                        "url"=>$baseUrl . '/' . $bannerIndex,
                        "action"=> $action,
                        "params"=>$bannerParams
                    );
            }
        }
        return $banner;
    }

    private function getBannerFromWeb()
    {
        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));
        $baseUrl = $this->request->getSchemeAndHttpHost();

        $this->banner = array();
        if (empty($blocks)) {
            return $banner;
        }

        $content = $this;
        //replace <a><img></a>
        $blocks = preg_replace_callback('/<a href=[\'\"](.*?)[\'\"]><img src=[\'\"](.*?)[\'\"][^>]\/><\/a>/', function($matches) use ($baseUrl, $content) {
            $matcheUrl = $matches[2];
            if (stripos($matcheUrl, "../") == 0) {
                $matcheUrl = substr($matcheUrl, 3);
            }
            $url = "${baseUrl}/$matcheUrl";
            $content->banner[] = array(
                "url"=>$url,
                "action"=>"webview",
                "params"=>$matches[1]
                );
            return '';
        }, $blocks['home_top_banner']);

        //replace img
        $blocks = preg_replace_callback('/<img src=[\'\"](.*?)[\'\"]>/', function($matches) use ($baseUrl, $content) {
            $matcheUrl = $matches[1];
            if (stripos($matcheUrl, "../")) {
                $matcheUrl = substr($matcheUrl, 3);
            }
            $url = "${baseUrl}/$matcheUrl";
            $content->banner[] = array(
                "url"=>$url,
                "action"=>"none",
                "params"=>''
                );
            return '';
        }, $blocks);
        return $this->banner;
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
            return $this->createErrorResponse('client_closed', '网校客户端未开启');
        }

        $site = $this->controller->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($this->controller->request, 2)
        );
        
        return $result;
    }

    private function getSchoolAnnouncementFromDb()
    {
        $result = array();
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

    private function sendRequest($method, $url, $params = array(), $ssl = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, "Suggestion Request");

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        if (strtoupper($method) == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url . (strpos($url, '?') ? '&' : '?') . http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url );

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}

