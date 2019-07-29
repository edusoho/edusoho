<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\SchoolProcessor;

class SchoolProcessorImpl extends BaseProcessor implements SchoolProcessor
{
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
            'site' => $this->getSiteInfo($this->controller->request, $version),
        );

        return $result;
    }

    public function getVipPayInfo()
    {
        if (!$this->controller->isinstalledPlugin('Vip')) {
            return $this->createErrorResponse('no_vip', '网校未安装vip插件');
        }

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $levelId = $this->getParam('levelId');
        $level = $this->getLevelService()->getLevel($levelId);

        $buyType = $this->controller->setting('vip.buyType');
        if (empty($buyType)) {
            $buyType = 10;
        }

        return array(
            'level' => $level,
            'buyType' => $buyType,
        );
    }

    public function getSchoolVipList()
    {
        $userId = $this->getParam('userId');
        if (!$this->controller->isinstalledPlugin('Vip')) {
            return $this->createErrorResponse('no_vip', '网校未安装vip插件');
        }

        if (!$this->controller->isinstalledPlugin('Vip') || !$this->controller->setting('vip.enabled')) {
            return $this->createMessageResponse('vip_closed', '会员专区已关闭');
        }

        $levels = $this->getLevelService()->searchLevels(array('enabled' => 1), array(), 0, 100);

        $levels = array_map(function ($level) {
            $level['picture'] = sprintf('/assets/img/default/vip_%d.png', $level['id']);

            return $level;
        }, $levels);

        $user = $this->controller->getUserService()->getUser($userId);

        return array(
            'user' => $this->controller->filterUser($user),
            'vips' => $levels,
        );
    }

    public function getSchoolPlugins()
    {
        $appsInstalled = $this->getAppService()->findApps(0, 100);
        $appsInstalled = ArrayToolkit::index($appsInstalled, 'code');

        foreach ($appsInstalled as $key => $value) {
            foreach ($value as $valueKey => $v) {
                if (!in_array($valueKey, array(
                    'id', 'version', 'type', ))) {
                    unset($value[$valueKey]);
                }

                $appsInstalled[$key] = $value;
            }
        }

        return $appsInstalled;
    }

    public function getSchoolApps()
    {
        return array();
    }

    public function registDevice()
    {
        $result = false;
        $parames = array();
        $parames['imei'] = $this->getParam('deviceSn', '');
        $parames['platform'] = $this->getParam('platform', '');
        $parames['version'] = $this->getParam('version', '');
        $parames['screenresolution'] = $this->getParam('screenresolution', '');
        $parames['kernel'] = $this->getParam('kernel', '');

        if (empty($parames['imei']) || empty($parames['platform'])) {
            return $this->createErrorResponse('info_error', '串号或平台版本不能为空!');
        }
        if ($this->getMobileDeviceService()->addMobileDevice($parames)) {
            $result = true;
        }

        $this->log('regist_device', '注册客户端', $parames);

        return true;
    }

    public function getFlashApk()
    {
        $version = (int) $this->request->query->get('version', 9);
        if ($version > 5 && $version < 14) {
            return $this->controller->redirect('http://mobcdn.qiniudn.com/flash_2.x_3.x.apk');
        }

        if ($version > 13 && $version < 16) {
            return $this->controller->redirect('http://mobcdn.qiniudn.com/flash_4.x.apk');
        }

        return '';
    }

    public function getDownloadUrl()
    {
        $code = $this->fixHttpHeaderInject($this->request->get('code', 'edusoho'));
        $client = $this->fixHttpHeaderInject($this->request->get('client', 'android'));

        $userAgent = $this->request->headers->get('user-agent');
        if (strpos($userAgent, 'MicroMessenger')) {
            return $this->controller->render('TopxiaMobileBundleV2:Content:download.html.twig', array());
        }

        return $this->controller->redirect("http://www.edusoho.com/download/mobile?client={$client}&code=".$code);
    }

    private function fixHttpHeaderInject($str)
    {
        if (empty($str)) {
            return $str;
        }

        return trim(strip_tags(preg_replace('/( |\t|\r|\n|\')/', '', $str)));
    }

    public function getClientVersion()
    {
        $code = $this->getParam('code', 'edusoho');
        $clientVersion = $this->sendRequest('GET', "http://www.edusoho.com/version/{$code}-android", array());
        if ('{}' == $clientVersion) {
            return null;
        }
        $clientVersion = json_decode($clientVersion);
        $result = array(
            'show' => 'alert' == $clientVersion->updateMode ? true : false,
            'code' => $clientVersion->versionCode,
            'androidVersion' => $clientVersion->version,
            'updateInfo' => $clientVersion->updateInfo,
            'updateUrl' => $clientVersion->url,
        );

        return $result;
    }

    public function suggestionLog()
    {
        $info = $this->getParam('info');
        $type = $this->getParam('type', 'bug');
        $contact = $this->getParam('contact');
        $domain = $this->getParam('domain');
        $accessKey = $this->getParam('accessKey');
        $name = $this->getParam('name');

        if (empty($info)) {
            return false;
        }

        $this->log('suggestion', '反馈内容', array(
            'info' => $info,
            'type' => $type,
            'contact' => $contact,
            'domain' => $domain,
            'accessKey' => $accessKey,
            'name' => $name,
        ));

        return true;
    }

    public function sendSuggestion()
    {
        $info = $this->getParam('info');
        $type = $this->getParam('type', 'bug');
        $contact = $this->getParam('contact');

        if (empty($info)) {
            return $this->createErrorResponse('error', '反馈内容不能为空！');
        }

        $site = $this->getSettingService()->get('site');
        $storage = $this->getSettingService()->get('storage');

        $this->sendRequest('POST', 'http://demo.edusoho.com/mapi_v2/School/suggestionLog', array(
            'info' => $info,
            'type' => $type,
            'contact' => $contact,
            'domain' => $site['url'],
            'accessKey' => $storage['cloud_access_key'],
            'name' => $site['name'],
        ));

        return true;
    }

    public function getShradCourseUrl()
    {
        $courseId = $this->request->get('courseId');
        if (empty($courseId)) {
            return new Response('课程不存在或已删除');
        }

        return $this->controller->redirect($this->controller->generateUrl('course_show', array('id' => $courseId)));
    }

    public function getUserterms()
    {
        $setting = $this->controller->getSettingService()->get('auth', array());
        $userTerms = '暂无服务条款';
        if (array_key_exists('user_terms_body', $setting)) {
            $userTerms = $setting['user_terms_body'];
        }

        return $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $userTerms,
        ));
    }

    public function getSchoolProfile()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());

        $content = $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $this->controller->convertAbsoluteUrl($this->request, $mobile['about']),
        ))->getContent();

        return array(
            'data' => $content,
        );
    }

    public function getSchoolInfo()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());

        return $this->controller->render('TopxiaMobileBundleV2:Content:index.html.twig', array(
            'content' => $this->controller->convertAbsoluteUrl($this->request, $mobile['about']),
        ));
    }

    public function getWeekRecommendCourses()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());

        $courseIds = explode(',', isset($mobile['courseIds']) ? $mobile['courseIds'] : '');
        $courses = $this->controller->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();
        foreach ($courseIds as $value) {
            if (!empty($value)) {
                if (array_key_exists($value, $courses)) {
                    $sortedCourses[] = $courses[$value];
                }
            }
        }

        $result = array(
            'start' => 0,
            'limit' => 3,
            'data' => $this->controller->filterCourses($sortedCourses), );

        return $result;
    }

    public function getHotCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status' => 'published',
            'type' => 'normal',
            'recommended' => 0,
        );

        return $this->getCourseByType('popular', $conditions);
    }

    public function getLiveRecommendCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status' => 'published',
            'type' => 'live',
            'recommended' => 1,
        );

        return $this->getCourseByType('recommendedSeq', $conditions);
    }

    public function getRecommendCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status' => 'published',
            'type' => 'normal',
            'recommended' => 1,
        );

        return $this->getCourseByType('recommendedSeq', $conditions);
    }

    public function getLiveLatestCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status' => 'published',
            'type' => 'live',
        );

        return $this->getCourseByType('latest', $conditions);
    }

    public function getLatestCourses()
    {
        $conditions = array(
            'parentId' => 0,
            'status' => 'published',
            'type' => 'normal',
        );

        return $this->getCourseByType('latest', $conditions);
    }

    private function getCourseByType($sort, $conditions)
    {
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $total = $this->getCourseService()->searchCourseCount($conditions);
        $courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);
        $result = array(
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->controller->filterCourses($courses), );

        return $result;
    }

    public function getSchoolAnnouncement()
    {
        $mobile = $this->getSettingService()->get('mobile', array());

        return array(
            'info' => $mobile['notice'],
            'action' => 'none',
            'params' => array(),
        );
    }

    public function getSchoolBanner()
    {
        $banner = array();
        $mobile = $this->getSettingService()->get('mobile', array());
        $baseUrl = $this->request->getSchemeAndHttpHost();
        $ssl = $this->request->isSecure() ? true : false;

        if (empty($mobile)) {
            return array();
        }

        for ($i = 1; $i < 6; ++$i) {
            if (empty($mobile['banner'.$i])) {
                continue;
            }

            $bannerIndex = $mobile['banner'.$i];

            if (!empty($bannerIndex)) {
                $bannerClick = empty($mobile['bannerClick'.$i]) ? '' : $mobile['bannerClick'.$i];
                $bannerParams = null;
                $action = 'none';
                switch ($bannerClick) {
                    case 0:
                        $action = 'none';
                        $bannerParams = null;
                        break;
                    case 1:
                        $action = 'webview';
                        if (array_key_exists('bannerUrl'.$i, $mobile)) {
                            $bannerParams = $mobile['bannerUrl'.$i];
                        } else {
                            $bannerParams = '';
                        }
                        break;
                    case 2:
                        $action = 'course';
                        if (array_key_exists('bannerJumpToCourseId'.$i, $mobile)) {
                            $bannerParams = $mobile['bannerJumpToCourseId'.$i];
                        } else {
                            $bannerParams = '';
                        }
                        break;
                }

                if (false !== strpos($bannerIndex, 'http://') || false !== strpos($bannerIndex, 'https://')) {
                    $uri = $bannerIndex;
                } elseif (preg_match('/^\/\/\s*/', $bannerIndex)) {
                    $uri = ($ssl ? 'https:' : 'http:').$bannerIndex;
                } else {
                    $uri = $baseUrl.'/'.$bannerIndex;
                }

                $banner[] = array(
                    'url' => $uri,
                    'action' => $action,
                    'params' => $bannerParams,
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
        $blocks = preg_replace_callback('/<a href=[\'\"](.*?)[\'\"]><img src=[\'\"](.*?)[\'\"][^>]\/><\/a>/', function ($matches) use ($baseUrl, $content) {
            $matcheUrl = $matches[2];
            if (0 == stripos($matcheUrl, '../')) {
                $matcheUrl = substr($matcheUrl, 3);
            }
            $url = "${baseUrl}/$matcheUrl";
            $content->banner[] = array(
                'url' => $url,
                'action' => 'webview',
                'params' => $matches[1],
            );

            return '';
        }, $blocks['home_top_banner']);

        //replace img
        $blocks = preg_replace_callback('/<img src=[\'\"](.*?)[\'\"]>/', function ($matches) use ($baseUrl, $content) {
            $matcheUrl = $matches[1];
            if (stripos($matcheUrl, '../')) {
                $matcheUrl = substr($matcheUrl, 3);
            }
            $url = "${baseUrl}/$matcheUrl";
            $content->banner[] = array(
                'url' => $url,
                'action' => 'none',
                'params' => '',
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
        if (empty($token) || self::TOKEN_TYPE != $token['type']) {
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
            'site' => $this->getSiteInfo($request),
        );

        return $result;
    }

    public function getSchoolSite()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '网校客户端未开启');
        }

        $site = $this->controller->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($this->controller->request, 2),
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
                'url' => '',
                'action' => 'none',
                'params' => array(),
            ),
            array(
                'url' => '',
                'action' => 'none',
                'params' => array(),
            ),
            array(
                'url' => '',
                'action' => 'none',
                'params' => array(),
            ),
        );

        return $banner;
    }

    private function sendRequest($method, $url, $params = array(), $ssl = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'Suggestion Request');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ('POST' == strtoupper($method)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getLevelService()
    {
        return $this->controller->getService('VipPlugin:Vip:LevelService');
    }
}
