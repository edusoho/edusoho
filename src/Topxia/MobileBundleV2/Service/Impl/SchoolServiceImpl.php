<?php


namespace Topxia\MobileBundleV2\Service\Impl;
use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\SchoolService;
use Symfony\Component\HttpFoundation\Response;

class SchoolServiceImpl extends BaseService implements SchoolService {

    public $banner;

    public function sendSuggestion()
    {
        $info = $this->getParam("info");
        $type = $this->getParam("type", 'bug');
        $contact = $this->getParam("contact");

        if (empty($info)) {
            return $this->createErrorResponse('error', '反馈内容不能为空！');
        }
        $url = "";
        try {
                $file = $this->request->files->get('file');
                $record = $this->getFileService()->uploadFile('course', $file);
                $url = $this->controller->get('topxia.twig.web_extension')->getFilePath($record['uri']);
                
        } catch (\Exception $e) {
                $url = "error";
        }

        $url = "<br><img src=''{$url}/>";
        $info = $info . $url;
        return $info;
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
        $sort = "recommend";
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);
        $courses = $this->controller->getCourseService()->searchCourses(array(), $sort, $start,  $limit);
        $result = array(
            "start"=>$start,
            "limit"=>$limit,
            "data"=>$this->controller->filterCourses($courses));
        return $result;
    }

    public function getRecommendCourses()
    {
        return $this->getCourseByType("recommendedSeq");
    }

    public function getLatestCourses()
    {
        return $this->getCourseByType("latest");
    }

    private function getCourseByType($sort)
    {
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);

        $conditions = array(
            'status' => 'published',
            'type' => 'normal',
        );
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
        return array(
            "info"=>"这是网校简介",
            "action"=>"none",
            "params"=>array()
            );
    }

    public function getSchoolBanner()
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
            $url = "${baseUrl}/$matches[2]";
            $content->banner[] = array(
                "url"=>$url,
                "action"=>"webview",
                "params"=>$matches[1]
                );
            return '';
        }, $blocks['home_top_banner']);

        //replace img
        $blocks = preg_replace_callback('/<img src=[\'\"](.*?)[\'\"]>/', function($matches) use ($baseUrl, $content) {
            $url = "${baseUrl}/$matches[1]";
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
            return $this->createErrorResponse('client_closed', '没有搜索到该网校！');
        }

        $site = $this->controller->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($this->controller->request)
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
}

