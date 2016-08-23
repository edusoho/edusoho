<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Common\ConvertIpToolkit;
use Topxia\Common\ExtensionManager;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Component\ShareSdk\WeixinShare;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Service\Util\HTMLPurifierFactory;

class WebExtension extends \Twig_Extension
{
    protected $container;

    protected $pageScripts;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('smart_time', array($this, 'smarttimeFilter')),
            new \Twig_SimpleFilter('date_format', array($this, 'dateformatFilter')),
            new \Twig_SimpleFilter('time_range', array($this, 'timeRangeFilter')),
            new \Twig_SimpleFilter('remain_time', array($this, 'remainTimeFilter')),
            new \Twig_SimpleFilter('location_text', array($this, 'locationTextFilter')),
            new \Twig_SimpleFilter('tags_html', array($this, 'tagsHtmlFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('file_size', array($this, 'fileSizeFilter')),
            new \Twig_SimpleFilter('plain_text', array($this, 'plainTextFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('sub_text', array($this, 'subTextFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('duration', array($this, 'durationFilter')),
            new \Twig_SimpleFilter('duration_text', array($this, 'durationTextFilter')),
            new \Twig_SimpleFilter('tags_join', array($this, 'tagsJoinFilter')),
            new \Twig_SimpleFilter('navigation_url', array($this, 'navigationUrlFilter')),
            new \Twig_SimpleFilter('chr', array($this, 'chrFilter')),
            new \Twig_SimpleFilter('bbCode2Html', array($this, 'bbCode2HtmlFilter')),
            new \Twig_SimpleFilter('score_text', array($this, 'scoreTextFilter')),
            new \Twig_SimpleFilter('simple_template', array($this, 'simpleTemplateFilter')),
            new \Twig_SimpleFilter('fill_question_stem_text', array($this, 'fillQuestionStemTextFilter')),
            new \Twig_SimpleFilter('fill_question_stem_html', array($this, 'fillQuestionStemHtmlFilter')),
            new \Twig_SimpleFilter('get_course_id', array($this, 'getCourseidFilter')),
            new \Twig_SimpleFilter('purify_html', array($this, 'getPurifyHtml')),
            new \Twig_SimpleFilter('purify_and_trim_html', array($this, 'getPurifyAndTrimHtml')),
            new \Twig_SimpleFilter('file_type', array($this, 'getFileType')),
            new \Twig_SimpleFilter('at', array($this, 'atFilter')),
            new \Twig_SimpleFilter('copyright_less', array($this, 'removeCopyright')),
            new \Twig_SimpleFilter('array_merge', array($this, 'arrayMerge')),
            new \Twig_SimpleFilter('space2nbsp', array($this, 'spaceToNbsp')),
            new \Twig_SimpleFilter('number_to_human', array($this, 'numberFilter')),
            new \Twig_SimpleFilter('array_column', array($this, 'arrayColumn'))
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('theme_global_script', array($this, 'getThemeGlobalScript')),
            new \Twig_SimpleFunction('file_uri_parse', array($this, 'parseFileUri')),
            // file_path 即将废弃，不要再使用
            new \Twig_SimpleFunction('file_path', array($this, 'getFilePath')),
            // default_path 即将废弃，不要再使用
            new \Twig_SimpleFunction('default_path', array($this, 'getDefaultPath')),
            // file_url 即将废弃，不要再使用
            new \Twig_SimpleFunction('file_url', array($this, 'getFileUrl')),

            new \Twig_SimpleFunction('system_default_path', array($this, 'getSystemDefaultPath')),
            new \Twig_SimpleFunction('fileurl', array($this, 'getFurl')),
            new \Twig_SimpleFunction('filepath', array($this, 'getFpath')),
            new \Twig_SimpleFunction('lazy_img', array($this, 'makeLazyImg'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('object_load', array($this, 'loadObject')),
            new \Twig_SimpleFunction('setting', array($this, 'getSetting')),
            new \Twig_SimpleFunction('set_price', array($this, 'getSetPrice')),
            new \Twig_SimpleFunction('percent', array($this, 'calculatePercent')),
            new \Twig_SimpleFunction('category_choices', array($this, 'getCategoryChoices')),
            new \Twig_SimpleFunction('dict', array($this, 'getDict')),
            new \Twig_SimpleFunction('dict_text', array($this, 'getDictText'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('upload_max_filesize', array($this, 'getUploadMaxFilesize')),
            new \Twig_SimpleFunction('js_paths', array($this, 'getJsPaths')),
            new \Twig_SimpleFunction('is_plugin_installed', array($this, 'isPluginInstalled')),
            new \Twig_SimpleFunction('plugin_version', array($this, 'getPluginVersion')),
            new \Twig_SimpleFunction('version_compare', array($this, 'versionCompare')),
            new \Twig_SimpleFunction('is_exist_in_subarray_by_id', array($this, 'isExistInSubArrayById')),
            new \Twig_SimpleFunction('context_value', array($this, 'getContextValue')),
            new \Twig_SimpleFunction('is_feature_enabled', array($this, 'isFeatureEnabled')),
            new \Twig_SimpleFunction('parameter', array($this, 'getParameter')),
            new \Twig_SimpleFunction('upload_token', array($this, 'makeUpoadToken')),
            new \Twig_SimpleFunction('countdown_time', array($this, 'getCountdownTime')),
            new \Twig_SimpleFunction('convertIP', array($this, 'getConvertIP')),
            new \Twig_SimpleFunction('isHide', array($this, 'isHideThread')),
            new \Twig_SimpleFunction('userOutCash', array($this, 'getOutCash')),
            new \Twig_SimpleFunction('userInCash', array($this, 'getInCash')),
            new \Twig_SimpleFunction('userAccount', array($this, 'getAccount')),
            new \Twig_SimpleFunction('blur_phone_number', array($this, 'blur_phone_number')),
            new \Twig_SimpleFunction('blur_idcard_number', array($this, 'blur_idcard_number')),
            new \Twig_SimpleFunction('sub_str', array($this, 'subStr')),
            new \Twig_SimpleFunction('load_script', array($this, 'loadScript')),
            new \Twig_SimpleFunction('export_scripts', array($this, 'exportScripts')),
            new \Twig_SimpleFunction('order_payment', array($this, 'getOrderPayment')),
            new \Twig_SimpleFunction('classroom_permit', array($this, 'isPermitRole')),
            new \Twig_SimpleFunction('crontab_next_executed_time', array($this, 'getNextExecutedTime')),
            new \Twig_SimpleFunction('finger_print', array($this, 'getFingerprint')),
            new \Twig_SimpleFunction('get_parameters_from_url', array($this, 'getParametersFromUrl')),
            new \Twig_SimpleFunction('is_trial', array($this, 'isTrial')),
            new \Twig_SimpleFunction('timestamp', array($this, 'timestamp')),
            new \Twig_SimpleFunction('get_user_vip_level', array($this, 'getUserVipLevel')),
            new \Twig_SimpleFunction('is_without_network', array($this, 'isWithoutNetwork')),
            new \Twig_SimpleFunction('render_notification', array($this, 'renderNotification')),
            new \Twig_SimpleFunction('route_exsit', array($this, 'routeExists')),
            new \Twig_SimpleFunction('is_micro_messenger', array($this, 'isMicroMessenger')),
            new \Twig_SimpleFunction('wx_js_sdk_config', array($this, 'weixinConfig'))
        );
    }

    public function weixinConfig()
    {
        $weixinmob_enabled = $this->getSetting('login_bind.weixinmob_enabled');
        if (!(bool) $weixinmob_enabled) {
            return null;
        }
        $jsApiTicket = ServiceKernel::instance()->createService('User.TokenService')->getTokenByType('jsapi.ticket');

        $key    = $this->getSetting('login_bind.weixinmob_key');
        $secret = $this->getSetting('login_bind.weixinmob_secret');
        if (empty($jsApiTicket)) {
            $config      = array('key' => $key, 'secret' => $secret);
            $weixinshare = new WeixinShare($config);
            $token       = $weixinshare->getJsApiTicket();

            $jsApiTicket = ServiceKernel::instance()->createService('User.TokenService')->makeToken(
                'jsapi.ticket',
                array('data' => $token, 'duration' => $token['expires_in'])
            );
        }

        $config = array(
            'appId'     => $key,
            'timestamp' => time(),
            'nonceStr'  => uniqid($prefix = "edusoho"),
            'jsApiList' => array('onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQZone', 'onMenuShareQQ')
        );

        $jsapi_ticket        = $jsApiTicket['data']['ticket'];
        $url                 = $this->container->get('request')->getUri();
        $string              = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$config['nonceStr']."&timestamp=".$config['timestamp']."&url=".$url;
        $config['string']    = $string;
        $config['signature'] = sha1($string);
        return json_encode($config);
    }

    public function renderNotification($notification)
    {
        if ($notification) {
            $manager                 = ExtensionManager::instance();
            $notification['message'] = $manager->renderNotification($notification);
        }

        return $notification;
    }

    public function routeExists($name)
    {
        $router = $this->container->get('router');
        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }

    public function isWithoutNetwork()
    {
        $network = $this->getSetting('developer.without_network', $default = false);
        return (bool) $network;
    }

    public function getUserVipLevel($userId)
    {
        return ServiceKernel::instance()->createService('Vip:Vip.VipService')->getMemberByUserId($userId);
    }

    public function getParametersFromUrl($url)
    {
        $BaseUrl = parse_url($url);

        if (isset($BaseUrl['query'])) {
            if (strstr($BaseUrl['query'], '&')) {
                $parameter  = explode('&', $BaseUrl['query']);
                $parameters = array();

                foreach ($parameter as $key => $value) {
                    $parameters[$key] = explode('=', $value);
                }
            } else {
                $parameter     = explode('=', $BaseUrl['query']);
                $parameters    = array();
                $parameters[0] = $parameter;
            }
        } else {
            return null;
        }

        return $parameters;
    }

    public function spaceToNbsp($content)
    {
        $content = str_replace(" ", "&nbsp;", $content);
        return $content;
    }

    public function isMicroMessenger()
    {
        return strpos($this->container->get('request')->headers->get('User-Agent'), 'MicroMessenger') !== false;
    }

    public function getFingerprint()
    {
        $user = $this->getUserService()->getCurrentUser();

        if (!$user->isLogin()) {
            return '';
        }

        $user = $this->getUserService()->getUser($user["id"]);

        // @todo 如果配置用户的关键信息，这个方法存在信息泄漏风险，更换新播放器后解决这个问题。
        $pattern = $this->getSetting('magic.video_fingerprint');

        if ($pattern) {
            $fingerprint = $this->parsePattern($pattern, $user);
        } else {
            $request     = $this->container->get('request');
            $host        = $request->getHttpHost();
            $fingerprint = "{$host} {$user['nickname']}";
        }

        return $fingerprint;
    }

    protected function parsePattern($pattern, $user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);

        $values = array_merge($user, $profile);
        $values = array_filter($values, function ($value) {
            return !is_array($value);
        }

        );
        return $this->simpleTemplateFilter($pattern, $values);
    }

    public function subStr($text, $start, $length)
    {
        $text = trim($text);

        $length = (int) $length;

        if (($length > 0) && (mb_strlen($text) > $length)) {
            $text = mb_substr($text, $start, $length, 'UTF-8');
        }

        return $text;
    }

    public function getOutCash($userId, $timeType = "oneWeek")
    {
        $time      = $this->filterTime($timeType);
        $condition = array(
            'userId'    => $userId,
            'type'      => "outflow",
            'cashType'  => 'Coin',
            'startTime' => $time
        );

        return ServiceKernel::instance()->createService('Cash.CashService')->analysisAmount($condition);
    }

    public function getInCash($userId, $timeType = "oneWeek")
    {
        $time      = $this->filterTime($timeType);
        $condition = array(
            'userId'    => $userId,
            'type'      => "inflow",
            'cashType'  => 'Coin',
            'startTime' => $time
        );
        return ServiceKernel::instance()->createService('Cash.CashService')->analysisAmount($condition);
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    public function getAccount($userId)
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService')->getAccountByUserId($userId);
    }

    private function filterTime($type)
    {
        $time = 0;

        switch ($type) {
            case 'oneWeek':
                $time = time() - 7 * 3600 * 24;
                break;
            case 'oneMonth':
                $time = time() - 30 * 3600 * 24;
                break;
            case 'threeMonths':
                $time = time() - 90 * 3600 * 24;
                break;
            default:
                break;
        }

        return $time;
    }

    private function getUserById($userId)
    {
        return ServiceKernel::instance()->createService('User.UserService')->getUser($userId);
    }

    public function isExistInSubArrayById($currentTarget, $targetArray)
    {
        foreach ($targetArray as $target) {
            if ($currentTarget['id'] == $target['id']) {
                return true;
            }
        }

        return false;
    }

    public function getThemeGlobalScript()
    {
        $theme    = $this->getSetting('theme.uri', 'default');
        $filePath = realpath($this->container->getParameter('kernel.root_dir')."/../web/themes/{$theme}/js/global-script.js");

        if ($filePath) {
            return 'theme/global-script';
        }

        return '';
    }

    public function isPluginInstalled($name)
    {
        $plugins = $this->container->get('kernel')->getPlugins();

        foreach ($plugins as $plugin) {
            if (is_array($plugin)) {
                if (strtolower($name) == strtolower($plugin['code'])) {
                    return true;
                }
            } else {
                if (strtolower($name) == strtolower($plugin)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPluginVersion($name)
    {
        $plugins = $this->container->get('kernel')->getPlugins();

        foreach ($plugins as $plugin) {
            if (strtolower($plugin['code']) == strtolower($name)) {
                return $plugin['version'];
            }
        }

        return null;
    }

    public function versionCompare($version1, $version2, $operator)
    {
        return version_compare($version1, $version2, $operator);
    }

    public function getJsPaths()
    {
        $basePath = $this->container->get('request')->getBasePath();
        $theme    = $this->getSetting('theme.uri', 'default');

        $plugins = $this->container->get('kernel')->getPlugins();
        $names   = array();

        foreach ($plugins as $plugin) {
            if (is_array($plugin)) {
                if ($plugin['type'] != 'plugin') {
                    continue;
                }

                $names[] = $plugin['code'];
            } else {
                $names[] = $plugin;
            }
        }

        $names[] = "customweb";
        $names[] = "customadmin";
        $names[] = 'topxiaweb';
        $names[] = 'topxiaadmin';
        $names[] = 'classroom';
        $names[] = 'materiallib';
        $names[] = 'sensitiveword';
        $names[] = 'org';

        $paths = array(
            'common' => 'common',
            'theme'  => "{$basePath}/themes/{$theme}/js"
        );

        foreach ($names as $name) {
            $name                   = strtolower($name);
            $paths["{$name}bundle"] = "{$basePath}/bundles/{$name}/js";
        }

        // $paths['balloon-video-player'] = 'http://player-cdn.edusoho.net/balloon-video-player';

        return $paths;
    }

    public function getContextValue($context, $key)
    {
        $keys  = explode('.', $key);
        $value = $context;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                throw new \InvalidArgumentException(sprintf("Key `%s` is not in context with %s", $key, implode(array_keys($context), ', ')));
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function isFeatureEnabled($feature)
    {
        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();
        return in_array($feature, $features);
    }

    public function getParameter($name, $default = null)
    {
        if (!$this->container->hasParameter($name)) {
            return $default;
        }

        return $this->container->getParameter($name);
    }

    public function makeUpoadToken($group, $type = 'image', $duration = 18000)
    {
        $maker = new UploadToken();
        return $maker->make($group, $type, $duration);
    }

    public function getConvertIP($ip)
    {
        if (!empty($ip)) {
            $location = ConvertIpToolkit::convertIp($ip);

            if ($location === 'INNA') {
                return '未知区域';
            }

            return $location;
        }

        return '';
    }

    public function dateformatFilter($time, $format = '')
    {
        if (empty($time)) {
            return;
        }

        if (empty($format)) {
            return date('Y-m-d H:i', $time);
        }

        return date($format, $time);
    }

    public function smarttimeFilter($time)
    {
        $diff = time() - $time;

        if ($diff < 0) {
            return '未来';
        }

        if ($diff == 0) {
            return '刚刚';
        }

        if ($diff < 60) {
            return $diff.'秒前';
        }

        if ($diff < 3600) {
            return round($diff / 60).'分钟前';
        }

        if ($diff < 86400) {
            return round($diff / 3600).'小时前';
        }

        if ($diff < 2592000) {
            return round($diff / 86400).'天前';
        }

        if ($diff < 31536000) {
            return date('m-d', $time);
        }

        return date('Y-m-d', $time);
    }

    public function remainTimeFilter($value, $timeType = '')
    {
        $remainTime = "";
        $remain     = $value - time();

        if ($remain <= 0 && empty($timeType)) {
            return $remainTime['second'] = '0分钟';
        }

        if ($remain <= 3600 && empty($timeType)) {
            return $remainTime['minutes'] = round($remain / 60).'分钟';
        }

        if ($remain < 86400 && empty($timeType)) {
            return $remainTime['hours'] = round($remain / 3600).'小时';
        }

        $remainTime['day'] = round(($remain < 0 ? 0 : $remain) / 86400).'天';

        if (!empty($timeType)) {
            return $remainTime[$timeType];
        } else {
            return $remainTime['day'];
        }
    }

    public function getCountdownTime($value)
    {
        $countdown = array('days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);

        $remain = $value - time();

        if ($remain <= 0) {
            return $countdown;
        }

        $countdown['days'] = intval($remain / 86400);
        $remain            = $remain - 86400 * $countdown['days'];

        $countdown['hours'] = intval($remain / 3600);
        $remain             = $remain - 3600 * $countdown['hours'];

        $countdown['minutes'] = intval($remain / 60);
        $remain               = $remain - 60 * $countdown['minutes'];

        $countdown['seconds'] = $remain;

        return $countdown;
    }

    public function durationFilter($value)
    {
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;
        return sprintf('%02d', $minutes).':'.sprintf('%02d', $seconds);
    }

    public function durationTextFilter($value)
    {
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;

        if ($minutes === 0) {
            return $seconds.'秒';
        }

        return "{$minutes}分钟{$seconds}秒";
    }

    public function timeRangeFilter($start, $end)
    {
        $range = date('Y年n月d日 H:i', $start).' - ';

        if ($this->container->get('topxia.timemachine')->inSameDay($start, $end)) {
            $range .= date('H:i', $end);
        } else {
            $range .= date('Y年n月d日 H:i', $end);
        }

        return $range;
    }

    public function tagsJoinFilter($tagIds)
    {
        if (empty($tagIds) || !is_array($tagIds)) {
            return '';
        }

        $tags  = ServiceKernel::instance()->createService('Taxonomy.TagService')->findTagsByIds($tagIds);
        $names = ArrayToolkit::column($tags, 'name');

        return join($names, ',');
    }

    public function navigationUrlFilter($url)
    {
        $url = (string) $url;

        if (strpos($url, '://')) {
            return $url;
        }

        if (!empty($url[0]) && ($url[0] == '/')) {
            return $url;
        }

        return $this->container->get('request')->getBaseUrl().'/'.$url;
    }

    /**
     *                            P -> 省全称,     p -> 省简称
     *                            C -> 城市全称,    c -> 城市简称
     *                            D -> 区全称,     d -> 区简称
     * @param  [type] $districeId     [description]
     * @param  string $format         格式，默认格式'P C D'。
     * @return [type] [description]
     */
    public function locationTextFilter($districeId, $format = 'P C D')
    {
        $text  = '';
        $names = ServiceKernel::instance()->createService('Taxonomy.LocationService')->getLocationFullName($districeId);

        $len = strlen($format);

        for ($i = 0; $i < $len; $i++) {
            switch ($format[$i]) {
                case 'P':
                    $text .= $names['province'];
                    break;

                case 'p':
                    $text .= $this->mb_trim($names['province'], '省');
                    break;

                case 'C':
                    $text .= $names['city'];
                    break;

                case 'c':
                    $text .= $this->mb_trim($names['city'], '市');
                    break;

                case 'D':
                case 'd':
                    $text .= $names['district'];
                    break;

                default:
                    $text .= $format[$i];
                    break;
            }
        }

        return $text;
    }

    public function tagsHtmlFilter($tags, $class = '')
    {
        $links = array();
        $tags  = ServiceKernel::instance()->createService('Taxonomy.TagService')->findTagsByIds($tags);

        foreach ($tags as $tag) {
            $url     = $this->container->get('router')->generate('course_explore', array('tagId' => $tag['id']));
            $links[] = "<a href=\"{$url}\" class=\"{$class}\">{$tag['name']}</a>";
        }

        return implode(' ', $links);
    }

    public function parseFileUri($uri)
    {
        $kernel = ServiceKernel::instance();
        return $kernel->createService('Content.FileService')->parseFileUri($uri);
    }

    public function getFilePath($uri, $default = '', $absolute = false)
    {
        $assets  = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $url = $assets->getUrl('assets/img/default/'.$default);

// $url = $request->getBaseUrl() . '/assets/img/default/' . $default;

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }

        if (strpos($uri, "http://") !== false) {
            return $uri;
        }

        $uri = $this->parseFileUri($uri);

        if ($uri['access'] == 'public') {
            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').'/'.$uri['path'];
            $url = ltrim($url, ' /');
            $url = $assets->getUrl($url);

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }
    }

    public function getDefaultPath($category, $uri = "", $size = '', $absolute = false)
    {
        $assets  = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $publicUrlpath = 'assets/img/default/';
            $url           = $assets->getUrl($publicUrlpath.$size.$category);

            $defaultSetting = ServiceKernel::instance()->createService('System.SettingService')->get('default', array());

            $key      = 'default'.ucfirst($category);
            $fileName = $key.'FileName';

            if (array_key_exists($key, $defaultSetting) && array_key_exists($fileName, $defaultSetting)) {
                if ($defaultSetting[$key] == 1) {
                    $url = $assets->getUrl($publicUrlpath.$size.$defaultSetting[$fileName]);
                }
            } elseif (array_key_exists($key, $defaultSetting) && $defaultSetting[$key]) {
                $uri = $defaultSetting[$size."Default".ucfirst($category)."Uri"];
            } else {
                return $url;
            }

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }

        return $this->parseUri($uri, $absolute);
    }

    private function parseUri($uri, $absolute = false)
    {
        if (strpos($uri, "http://") !== false) {
            return $uri;
        }

        $assets  = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (strpos($uri, '://')) {
            $uri = $this->parseFileUri($uri);
            $url = "";

            if ($uri['access'] == 'public') {
                $url = $uri['path'];
            }
        } else {
            $url = $uri;
        }

        $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').'/'.$url;
        $url = ltrim($url, ' /');
        $url = $assets->getUrl($url);

        return $this->addHost($url, $absolute);
    }

    public function getSystemDefaultPath($defaultKey, $absolute = false)
    {
        $assets         = $this->container->get('templating.helper.assets');
        $defaultSetting = $this->getSetting("default", array());

        if (array_key_exists($defaultKey, $defaultSetting)
            && $defaultSetting[$defaultKey]
        ) {
            $path = $defaultSetting[$defaultKey];
            return $this->parseUri($path, $absolute);
        } else {
            $path = $assets->getUrl('assets/img/default/'.$defaultKey);
            return $this->addHost($path, $absolute);
        }
    }

    public function makeLazyImg($src, $class = '', $alt = '', $img = 'lazyload_course.png')
    {
        $imgpath = $path = $this->container->get('templating.helper.assets')->getUrl('assets/img/default/'.$img);
        return sprintf('<img src="%s" alt="%s" class="%s" data-echo="%s" />', $imgpath, $alt, $class, $src);
    }

    public function loadScript($js)
    {
        $js = is_array($js) ? $js : array($js);

        if ($this->pageScripts) {
            $this->pageScripts = array_merge($this->pageScripts, $js);
        } else {
            $this->pageScripts = $js;
        }
    }

    public function exportScripts()
    {
        if (empty($this->pageScripts)) {
            $this->pageScripts = array();
        }

        return array_values(array_unique($this->pageScripts));
    }

    public function getFileUrl($uri, $default = '', $absolute = false)
    {
        $assets  = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $url = $assets->getUrl('assets/img/default/'.$default);

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }

        $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').'/'.$uri;
        $url = ltrim($url, ' /');
        $url = $assets->getUrl($url);

        if ($absolute) {
            $url = $request->getSchemeAndHttpHost().$url;
        }

        return $url;
    }

    public function getFurl($path, $defaultKey = false)
    {
        return $this->getPublicFilePath($path, $defaultKey, true);
    }

    public function getFpath($path, $defaultKey = false)
    {
        return $this->getPublicFilePath($path, $defaultKey, false);
    }

    private function getPublicFilePath($path, $defaultKey = false, $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');

        if (empty($path)) {
            $defaultSetting = $this->getSetting("default", array());

            if ((($defaultKey == 'course.png' && array_key_exists('defaultCoursePicture', $defaultSetting) && $defaultSetting['defaultCoursePicture'] == 1)
                || ($defaultKey == 'avatar.png' && array_key_exists('defaultAvatar', $defaultSetting) && $defaultSetting['defaultAvatar'] == 1))
                && (array_key_exists($defaultKey, $defaultSetting)
                    && $defaultSetting[$defaultKey])
            ) {
                $path = $defaultSetting[$defaultKey];
                return $this->parseUri($path, $absolute);
            } else {
                $path = $assets->getUrl('assets/img/default/'.$defaultKey);
                return $this->addHost($path, $absolute);
            }
        }

        return $this->parseUri($path, $absolute);
    }

    private function addHost($path, $absolute)
    {
        $cdn    = ServiceKernel::instance()->createService('System.SettingService')->get('cdn', array());
        $cdnUrl = (empty($cdn['enabled'])) ? '' : rtrim($cdn['url'], " \/");

        if ($cdnUrl) {
            $path = $cdnUrl.$path;
        } elseif ($absolute) {
            $request = $this->container->get('request');
            $path    = $request->getSchemeAndHttpHost().$path;
        }

        return $path;
    }

    public function fileSizeFilter($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps     = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);

        foreach ($unitExps as $unit => $exp) {
            $divisor      = pow(1000, $exp);
            $currentUnit  = $unit;
            $currentValue = $size / $divisor;

            if ($currentValue < 1000) {
                break;
            }
        }

        return sprintf('%.1f', $currentValue).$currentUnit;
    }

    public function numberFilter($number)
    {
        if ($number <= 1000) {
            return $number;
        }

        $currentValue = $currentUnit = null;
        $unitExps     = array('千' => 3, '万' => 4, '亿' => 8);

        foreach ($unitExps as $unit => $exp) {
            $divisor      = pow(10, $exp);
            $currentUnit  = $unit;
            $currentValue = $number / $divisor;

            if ($currentValue < 10) {
                break;
            }
        }

        return sprintf('%.0f', $currentValue).$currentUnit;
    }

    public function loadObject($type, $id)
    {
        $kernel = ServiceKernel::instance();

        switch ($type) {
            case 'user':
                return $kernel->createService('User.UserService')->getUser($id);
            case 'category':
                return $kernel->createService('Taxonomy.CategoryService')->getCategory($id);
            case 'course':
                return $kernel->createService('Course.CourseService')->getCourse($id);
            case 'file_group':
                return $kernel->createService('Content.FileService')->getFileGroup($id);
            default:
                return null;
        }
    }

    public function plainTextFilter($text, $length = null)
    {
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t"), '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);

        $length = (int) $length;

        if (($length > 0) && (mb_strlen($text) > $length)) {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public function subTextFilter($text, $length = null)
    {
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t"), '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);

        $length = (int) $length;

        if (($length > 0) && (mb_strlen($text, 'utf-8') > $length)) {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public function getFileType($fileName, $string = null)
    {
        $fileName = explode(".", $fileName);

        if ($string) {
            $name = strtolower($fileName[count($fileName) - 1]).$string;
        }

        return $name;
    }

    public function chrFilter($index)
    {
        return chr($index);
    }

    public function isHideThread($id)
    {
        $need = ServiceKernel::instance()->createService('Group.ThreadService')->sumGoodsCoinsByThreadId($id);

        $thread = ServiceKernel::instance()->createService('Group.ThreadService')->getThread($id);

        $data = explode('[/hide]', $thread['content']);

        foreach ($data as $key => $value) {
            $value = " ".$value;
            sscanf($value, "%[^[][hide=reply]%[^$$]", $replyContent, $replyHideContent);

            if ($replyHideContent) {
                return true;
            }
        }

        if ($need) {
            return true;
        }

        return false;
    }

    public function bbCode2HtmlFilter($bbCode)
    {
        $ext = $this;

        $bbCode = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function ($matches) use ($ext) {
            $src = $ext->getFileUrl($matches[1]);
            return "<img src='{$src}' />";
        }, $bbCode);

        $bbCode = preg_replace_callback('/\[audio.*?id="(\d+)"\](.*?)\[\/audio\]/i', function ($matches) {
            return "<span class='audio-play-trigger' href='javascript:;' data-file-id=\"{$matches[1]}\" data-file-type=\"audio\"></span>";
        }, $bbCode);

        return $bbCode;
    }

    public function scoreTextFilter($text)
    {
        $text = number_format($text, 1, '.', '');

        if (intval($text) == $text) {
            return (string) intval($text);
        }

        return $text;
    }

    public function simpleTemplateFilter($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        return $text;
    }

    public function fillQuestionStemTextFilter($stem)
    {
        return preg_replace('/\[\[.+?\]\]/', '____', $stem);
    }

    public function fillQuestionStemHtmlFilter($stem)
    {
        $index = 0;
        $stem  = preg_replace_callback('/\[\[.+?\]\]/', function ($matches) use (&$index) {
            $index++;
            return "<span class='question-stem-fill-blank'>({$index})</span>";
        }, $stem);
        return $stem;
    }

    public function getCourseidFilter($target)
    {
        $target = explode('/', $target);
        $target = explode('-', $target[0]);
        return $target[1];
    }

    public function getPurifyHtml($html, $trusted = false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => ServiceKernel::instance()->getParameter('kernel.cache_dir').'/htmlpurifier'
        );

        $factory  = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        return $purifier->purify($html);
    }

    public function atFilter($text, $ats = array())
    {
        if (empty($ats) || !is_array($ats)) {
            return $text;
        }

        $router = $this->container->get('router');

        foreach ($ats as $nickname => $userId) {
            $path = $router->generate('user_show', array('id' => $userId));
            $html = "<a href=\"{$path}\" data-uid=\"{$userId}\" target=\"_blank\">@{$nickname}</a>";

            $text = preg_replace("/@{$nickname}/ui", $html, $text);
        }

        return $text;
    }

    public function removeCopyright($source)
    {
        if ($this->getSetting('copyright.owned', false)) {
            $source = str_ireplace('edusoho', '', $source);
        }

        return $source;
    }

    public function getSetting($name, $default = null)
    {
        $names = explode('.', $name);

        $name = array_shift($names);

        if (empty($name)) {
            return $default;
        }

        $value = ServiceKernel::instance()->createService('System.SettingService')->get($name);

        if (!isset($value)) {
            return $default;
        }

        if (empty($names)) {
            return $value;
        }

        $result = $value;

        foreach ($names as $name) {
            if (!isset($result[$name])) {
                return $default;
            }

            $result = $result[$name];
        }

        return $result;
    }

    public function getOrderPayment($order, $default = null)
    {
        $coinSettings = ServiceKernel::instance()->createService('System.SettingService')->get('coin', array());

        if (!isset($coinSettings['price_type'])) {
            $coinSettings['price_type'] = "RMB";
        }

        if (!isset($coinSettings['coin_enabled'])) {
            $coinSettings['coin_enabled'] = 0;
        }

        if ($coinSettings['coin_enabled'] != 1 || $coinSettings['price_type'] != 'coin') {
            if ($order['coinAmount'] > 0 && $order['amount'] == 0) {
                $default = '余额支付';
            } else {
                if ($order['amount'] == 0) {
                    $default = "无";
                } else {
                    $default = $this->getDictText('payment', $order['payment']);
                }
            }
        }

        return $default;
    }

    public function isPermitRole($classroomId, $permission, $isStudentOrAuditor = false)
    {
        $funcName = 'can'.$permission.'Classroom';

        if ($isStudentOrAuditor) {
            return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->$funcName($classroomId, $isStudentOrAuditor);
        }

        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->$funcName($classroomId);
    }

    public function calculatePercent($number, $total)
    {
        if ($number == 0 || $total == 0) {
            return '0%';
        }

        if ($number >= $total) {
            return '100%';
        }

        return intval($number / $total * 100).'%';
    }

    public function arrayMerge($text, $content)
    {
        $array = array_merge($text, $content);
        return $array;
    }

    public function getSetPrice($price)
    {
        return NumberToolkit::roundUp($price);
    }

    public function getCategoryChoices($groupName, $indent = '　')
    {
        $builder = new CategoryBuilder();
        return $builder->buildChoices($groupName, $indent);
    }

    public function getDict($type)
    {
        return ExtensionManager::instance()->getDataDict($type);
    }

    public function getDictText($type, $key)
    {
        $dict = $this->getDict($type);

        if (empty($dict) || !isset($dict[$key])) {
            return '';
        }

        return $dict[$key];
    }

    public function getNextExecutedTime()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService')->getNextExcutedTime();
    }

    public function getUploadMaxFilesize($formated = true)
    {
        $max = FileToolkit::getMaxFilesize();

        if ($formated) {
            return FileToolkit::formatFileSize($max);
        }

        return $max;
    }

    public function getName()
    {
        return 'topxia_web_twig';
    }

    public function isTrial()
    {
        if (file_exists(__DIR__.'/../../../../../app/data/trial.lock')) {
            return true;
        }

        return false;
    }

    public function timestamp()
    {
        return time();
    }

    public function blur_phone_number($phoneNum)
    {
        $head = substr($phoneNum, 0, 3);
        $tail = substr($phoneNum, -4, 4);
        return ($head.'****'.$tail);
    }

    public function blur_idcard_number($idcardNum)
    {
        $head = substr($idcardNum, 0, 4);
        $tail = substr($idcardNum, -2, 2);
        return ($head.'************'.$tail);
    }

    public function getPurifyAndTrimHtml($html)
    {
        $html = strip_tags($html, '');

        return preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $html);
    }

    public function arrayColumn($array, $column)
    {
        return ArrayToolkit::column($array, $column);
    }

    public function mb_trim($string, $charlist = '\\\\s', $ltrim = true, $rtrim = true)
    {
        $bothEnds = $ltrim && $rtrim;

        $charClassInner = preg_replace(
            array('/[\^\-\]\\\]/S', '/\\\{4}/S'),
            array('\\\\\\0', '\\'),
            $charlist
        );

        $workHorse              = '['.$charClassInner.']+';
        $ltrim && $leftPattern  = '^'.$workHorse;
        $rtrim && $rightPattern = $workHorse.'$';

        if ($bothEnds) {
            $patternMiddle = $leftPattern.'|'.$rightPattern;
        } elseif ($ltrim) {
            $patternMiddle = $leftPattern;
        } else {
            $patternMiddle = $rightPattern;
        }

        return preg_replace("/$patternMiddle/usSD", '', $string);
    }
}
