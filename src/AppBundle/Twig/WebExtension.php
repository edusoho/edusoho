<?php

namespace AppBundle\Twig;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ConvertIpToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\ExtensionManager;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\NumberToolkit;
use AppBundle\Common\PluginVersionToolkit;
use AppBundle\Common\UserToolkit;
use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;
use AppBundle\Component\ShareSdk\WeixinShare;
use AppBundle\Util\CategoryBuilder;
use AppBundle\Util\CdnUrl;
use AppBundle\Util\UploadToken;
use Biz\Account\Service\AccountProxyService;
use Biz\Player\Service\PlayerService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\SimpleValidator;
use ApiBundle\Api\Util\AssetHelper;

class WebExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    protected $pageScripts;

    protected $locale;

    protected $defaultCloudSdkHost;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('smart_time', array($this, 'smarttimeFilter')),
            new \Twig_SimpleFilter('date_format', array($this, 'dateformatFilter')),
            new \Twig_SimpleFilter('time_range', array($this, 'timeRangeFilter')),
            new \Twig_SimpleFilter('time_diff', array($this, 'timeDiffFilter')),
            new \Twig_SimpleFilter('remain_time', array($this, 'remainTimeFilter')),
            new \Twig_SimpleFilter('time_formatter', array($this, 'timeFormatterFilter')),
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
            new \Twig_SimpleFilter('array_column', array($this, 'arrayColumn')),
            new \Twig_SimpleFilter('rename_locale', array($this, 'renameLocale')),
            new \Twig_SimpleFilter('cdn', array($this, 'cdn')),
            new \Twig_SimpleFilter('wrap', array($this, 'wrap')),
            new \Twig_SimpleFilter('convert_absolute_url', array($this, 'convertAbsoluteUrl')),
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
            // system_default_path，即将废弃，不要再使用
            new \Twig_SimpleFunction('system_default_path', array($this, 'getSystemDefaultPath')),
            new \Twig_SimpleFunction('fileurl', array($this, 'getFurl')),
            new \Twig_SimpleFunction('filepath', array($this, 'getFpath')),
            new \Twig_SimpleFunction('lazy_img', array($this, 'makeLazyImg'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('avatar_path', array($this, 'avatarPath')),
            new \Twig_SimpleFunction('object_load', array($this, 'loadObject')),
            new \Twig_SimpleFunction('setting', array($this, 'getSetting')),
            new \Twig_SimpleFunction('set_price', array($this, 'getSetPrice')),
            new \Twig_SimpleFunction('percent', array($this, 'calculatePercent')),
            new \Twig_SimpleFunction('category_choices', array($this, 'getCategoryChoices')),
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
            //todo covertIP 要删除
            new \Twig_SimpleFunction('convertIP', array($this, 'getConvertIP')),
            new \Twig_SimpleFunction('convert_ip', array($this, 'getConvertIP')),
            new \Twig_SimpleFunction('isHide', array($this, 'isHideThread')),
            new \Twig_SimpleFunction('user_coin_amount', array($this, 'userCoinAmount')),

            new \Twig_SimpleFunction('user_balance', array($this, 'getBalance')),

            new \Twig_SimpleFunction('blur_user_name', array($this, 'blurUserName')),
            new \Twig_SimpleFunction('blur_phone_number', array($this, 'blur_phone_number')),
            new \Twig_SimpleFunction('blur_idcard_number', array($this, 'blur_idcard_number')),
            new \Twig_SimpleFunction('blur_number', array($this, 'blur_number')),
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
            new \Twig_SimpleFunction('get_admin_roles', array($this, 'getAdminRoles')),
            new \Twig_SimpleFunction('render_notification', array($this, 'renderNotification')),
            new \Twig_SimpleFunction('route_exsit', array($this, 'routeExists')),
            new \Twig_SimpleFunction('is_micro_messenger', array($this, 'isMicroMessenger')),
            new \Twig_SimpleFunction('wx_js_sdk_config', array($this, 'weixinConfig')),
            new \Twig_SimpleFunction('plugin_update_notify', array($this, 'pluginUpdateNotify')),
            new \Twig_SimpleFunction('tag_equal', array($this, 'tagEqual')),
            new \Twig_SimpleFunction('array_index', array($this, 'arrayIndex')),
            new \Twig_SimpleFunction('cdn', array($this, 'getCdn')),
            new \Twig_SimpleFunction('is_show_mobile_page', array($this, 'isShowMobilePage')),
            new \Twig_SimpleFunction('is_mobile_client', array($this, 'isMobileClient')),
            new \Twig_SimpleFunction('is_ES_copyright', array($this, 'isESCopyright')),
            new \Twig_SimpleFunction('get_classroom_name', array($this, 'getClassroomName')),
            new \Twig_SimpleFunction('pop_reward_point_notify', array($this, 'popRewardPointNotify')),
            new \Twig_SimpleFunction('array_filter', array($this, 'arrayFilter')),
            new \Twig_SimpleFunction('base_path', array($this, 'basePath')),
            new \Twig_SimpleFunction('get_login_email_address', array($this, 'getLoginEmailAddress')),
            new \Twig_SimpleFunction('cloud_sdk_url', array($this, 'getCloudSdkUrl')),
            new \Twig_SimpleFunction('math_format', array($this, 'mathFormat')),
            new \Twig_SimpleFunction('parse_user_agent', array($this, 'parseUserAgent')),
            new \Twig_SimpleFunction('wechat_login_bind_enabled', array($this, 'isWechatLoginBind')),
            new \Twig_SimpleFunction('can_send_message', array($this, 'canSendMessage')),
            new \Twig_SimpleFunction('is_hidden_video_header', array($this, 'isHiddenVideoHeader')),
            new \Twig_SimpleFunction('arrays_key_convert', array($this, 'arraysKeyConvert')),
            new \Twig_SimpleFunction('is_system_generated_email', array($this, 'isSystemGeneratedEmail')),
        );
    }

    public function convertAbsoluteUrl($html)
    {
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function ($matches) {
            $cdn = new CdnUrl();
            $cdnUrl = $cdn->get('content');
            if (!empty($cdnUrl)) {
                $absoluteUrl = AssetHelper::getScheme().':'.rtrim($cdnUrl, '/').'/'.ltrim($matches[1], '/');
            } else {
                $absoluteUrl = AssetHelper::uriForPath('/'.ltrim($matches[1], '/'));
            }

            return "src=\"{$absoluteUrl}\"";
        }, $html);

        return $html;
    }

    public function parseUserAgent($userAgent)
    {
        $deviceDetector = new DeviceDetectorAdapter($userAgent);

        return array(
            'device' => $deviceDetector->getDevice(),
            'client' => $deviceDetector->getClient(),
            'os' => $deviceDetector->getOs(),
        );
    }

    public function arrayFilter($data, $filterName)
    {
        if (empty($data) || !is_array($data)) {
            return array();
        }

        return array_filter($data, function ($value) use ($filterName) {
            foreach ($filterName as $name) {
                if ('' === $value[$name]) {
                    return false;
                }
            }

            return true;
        });
    }

    public function isShowMobilePage()
    {
        $wapSetting = $this->getSetting('wap', array('version' => 0));

        if (empty($wapSetting['version'])) {
            return false;
        }

        $pcVersion = $this->container->get('request')->cookies->get('PCVersion', 0);
        if ($pcVersion) {
            return false;
        }

        return DeviceToolkit::isMobileClient();
    }

    public function isMobileClient()
    {
        return DeviceToolkit::isMobileClient();
    }

    public function isESCopyright()
    {
        $copyright = $this->getSetting('copyright');
        $request = $this->container->get('request');
        $host = $request->getHttpHost();
        if ($copyright) {
            $result = !(
                isset($copyright['owned'])
                && isset($copyright['thirdCopyright'])
                && 2 != $copyright['thirdCopyright']
                && isset($copyright['licenseDomains'])
                && in_array($host, explode(';', $copyright['licenseDomains']))
                || (isset($copyright['thirdCopyright']) && 2 == $copyright['thirdCopyright'])
            );

            return $result;
        }

        return true;
    }

    public function getClassroomName()
    {
        return $this->getSetting('classroom.name', $this->container->get('translator')->trans('site.default.classroom'));
    }

    public function tagEqual($tags, $targetTagId, $targetTagGroupId)
    {
        foreach ($tags as $groupId => $tagId) {
            if ($groupId == $targetTagGroupId && $tagId == $targetTagId) {
                return true;
            }
        }

        return false;
    }

    public function arrayIndex($array, $key)
    {
        if (empty($array) || !is_array($array)) {
            return array();
        }

        return ArrayToolkit::index($array, $key);
    }

    public function timeFormatterFilter($time)
    {
        if ($time <= 60) {
            return $this->trans('site.twig.extension.time_interval.minute', array('%diff%' => 0));
        }

        if ($time <= 3600) {
            return $this->trans('site.twig.extension.time_interval.minute', array('%diff%' => round($time / 60)));
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', array('%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)));
    }

    public function pluginUpdateNotify()
    {
        $count = $this->getAppService()->findAppCount();
        $apps = $this->getAppService()->findApps(0, $count);

        $apps = array_filter($apps, function ($app) {
            return 'EduSoho官方' == $app['developerName'];
        });
        $notifies = array_reduce(
            $apps,
            function ($notifies, $app) {
                if (!PluginVersionToolkit::dependencyVersion($app['code'], $app['version'])) {
                    $notifies[$app['type']][] = $app['name'];
                } elseif ('MAIN' !== $app['code'] && $app['protocol'] < 3) {
                    $notifies[$app['type']][] = $app['name'];
                }

                return $notifies;
            },
            array()
        );

        return $notifies;
    }

    public function getAdminRoles()
    {
        return $this->createService('Role:RoleService')->searchRoles(array(), 'created', 0, 1000);
    }

    public function getCdn($type = 'default')
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get($type);

        return $cdnUrl;
    }

    public function cdn($content)
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get('content');

        if ($cdnUrl) {
            $publicUrlPath = $this->container->getParameter('topxia.upload.public_url_path');
            $themeUrlPath = $this->container->getParameter('topxia.web_themes_url_path');
            $assetUrlPath = $this->container->getParameter('topxia.web_assets_url_path');
            $bundleUrlPath = $this->container->getParameter('topxia.web_bundles_url_path');
            $staticDistUrlPath = $this->container->getParameter('front_end.web_static_dist_url_path');
            preg_match_all('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $content, $imgs);
            if ($imgs) {
                $urls = array_unique($imgs[1]);
                foreach ($urls as $img) {
                    if (0 === strpos($img, $publicUrlPath)
                        || 0 === strpos($img, $themeUrlPath)
                        || 0 === strpos($img, $assetUrlPath)
                        || 0 === strpos($img, $bundleUrlPath)
                        || 0 === strpos($img, $staticDistUrlPath)) {
                        $content = str_replace('"'.$img, '"'.$cdnUrl.$img, $content);
                    }
                }
            }
        }

        return $content;
    }

    public function weixinConfig($url = '')
    {
        $weixinmob_enabled = $this->getSetting('login_bind.weixinmob_enabled');
        if (!(bool) $weixinmob_enabled) {
            return null;
        }
        $jsApiTicket = $this->createService('User:TokenService')->getTokenByType('jsapi.ticket');

        $key = $this->getSetting('login_bind.weixinmob_key');
        $secret = $this->getSetting('login_bind.weixinmob_secret');
        if (empty($jsApiTicket)) {
            $config = array('key' => $key, 'secret' => $secret);
            $weixinshare = new WeixinShare($config);
            $token = $weixinshare->getJsApiTicket();
            if (empty($token)) {
                return array();
            }

            $jsApiTicket = $this->createService('User:TokenService')->makeToken(
                'jsapi.ticket',
                array('data' => $token, 'duration' => $token['expires_in'])
            );
        }

        $config = array(
            'appId' => $key,
            'timestamp' => time(),
            'nonceStr' => uniqid($prefix = 'edusoho'),
            'jsApiList' => array('onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQZone', 'onMenuShareQQ'),
        );

        $jsapi_ticket = $jsApiTicket['data']['ticket'];
        $url = empty($url) ? $this->container->get('request')->getUri() : $url;
        $string = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$config['nonceStr'].'&timestamp='.$config['timestamp'].'&url='.$url;
        $config['string'] = $string;
        $config['signature'] = sha1($string);

        return json_encode($config);
    }

    public function renderNotification($notification)
    {
        if ($notification) {
            $manager = ExtensionManager::instance();
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
        return $this->createService('VipPlugin:Vip:VipService')->getMemberByUserId($userId);
    }

    public function getParametersFromUrl($url)
    {
        $BaseUrl = parse_url($url);

        if (isset($BaseUrl['query'])) {
            if (strstr($BaseUrl['query'], '&')) {
                $parameter = explode('&', $BaseUrl['query']);
                $parameters = array();

                foreach ($parameter as $key => $value) {
                    $parameters[$key] = explode('=', $value);
                }
            } else {
                $parameter = explode('=', $BaseUrl['query']);
                $parameters = array();
                $parameters[0] = $parameter;
            }
        } else {
            return null;
        }

        return $parameters;
    }

    public function spaceToNbsp($content)
    {
        $content = str_replace(' ', '&nbsp;', $content);

        return $content;
    }

    public function isMicroMessenger()
    {
        return false !== strpos($this->container->get('request')->headers->get('User-Agent'), 'MicroMessenger');
    }

    public function renameLocale($locale)
    {
        $locale = strtolower($locale);
        $locale = str_replace('_', '-', $locale);

        return 'zh-cn' == $locale ? '' : '-'.$locale;
    }

    public function getFingerprint()
    {
        $user = $this->getUserService()->getCurrentUser();

        if (!$user->isLogin()) {
            return '';
        }

        $user = $this->getUserService()->getUser($user['id']);

        // @todo 如果配置用户的关键信息，这个方法存在信息泄漏风险，更换新播放器后解决这个问题。
        $pattern = $this->getSetting('magic.video_fingerprint');

        if ($pattern) {
            $fingerprint = $this->parsePattern($pattern, $user);
        } else {
            $request = $this->container->get('request');
            $host = $request->getHttpHost();
            $fingerprint = "{$host} {$user['nickname']}";
        }

        return $fingerprint;
    }

    public function popRewardPointNotify()
    {
        $session = $this->container->get('session');

        if (empty($session)) {
            return '';
        }

        $message = $session->get('Reward-Point-Notify');

        $session->remove('Reward-Point-Notify');

        return $message;
    }

    protected function parsePattern($pattern, $user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);

        $values = array_merge($user, $profile);
        $values = array_filter(
            $values,
            function ($value) {
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

    public function userCoinAmount($type, $userId, $startDateTime = null, $endDateTime = null)
    {
        if (!empty($endDateTime)) {
            $condition['created_time_LTE'] = strtotime($endDateTime);
        }

        if (!empty($startDateTime)) {
            $condition['created_time_GTE'] = strtotime($startDateTime);
        }

        $condition = array(
            'user_id' => $userId,
            'type' => $type,
            'amount_type' => 'coin',
        );
        $amount = $this->getAccountProxyService()->sumColumnByConditions('amount', $condition);

        return $amount;
    }

    public function getBalance($userId)
    {
        $balance = $this->getAccountProxyService()->getUserBalanceByUserId($userId);

        return $balance;
    }

    /**
     * @return AccountProxyService
     */
    protected function getAccountProxyService()
    {
        return $this->createService('Account:AccountProxyService');
    }

    private function getUserService()
    {
        return $this->createService('User:UserService');
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
        $theme = $this->getSetting('theme.uri', 'default');
        $filePath = realpath(
            $this->container->getParameter('kernel.root_dir')."/../web/themes/{$theme}/js/global-script.js"
        );

        if ($filePath) {
            return 'theme/global-script';
        }

        return '';
    }

    public function isPluginInstalled($name)
    {
        return $this->container->get('kernel')->getPluginConfigurationManager()->isPluginInstalled($name);
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
        $cdnUrl = new CdnUrl();
        $basePath = $cdnUrl->get();

        if (empty($basePath)) {
            $basePath = $this->container->get('request')->getBasePath();
        }

        $theme = $this->getSetting('theme.uri', 'default');

        $plugins = $this->container->get('kernel')->getPlugins();
        $names = array();
        $newPluginNames = array();

        foreach ($plugins as $plugin) {
            if (is_array($plugin)) {
                if ('plugin' != $plugin['type']) {
                    continue;
                }

                if (isset($plugin['protocol']) && 3 == $plugin['protocol']) {
                    $newPluginNames[] = $plugin['code'].'plugin';
                } else {
                    $names[] = $plugin['code'];
                }
            } else {
                $names[] = $plugin;
            }
        }

        $names[] = 'customweb';
        $names[] = 'customadmin';
        $names[] = 'custom';
        $names[] = 'topxiaweb';
        $names[] = 'topxiaadmin';
        $names[] = 'classroom';
        $names[] = 'materiallib';
        $names[] = 'sensitiveword';
        $names[] = 'permission';
        $names[] = 'org';

        $paths = array(
            'common' => 'common',
            'theme' => "{$basePath}/themes/{$theme}/js",
        );

        foreach ($names as $name) {
            $name = strtolower($name);
            $paths["{$name}bundle"] = "{$basePath}/bundles/{$name}/js";
        }

        foreach ($newPluginNames as $newPluginName) {
            $newPluginName = strtolower($newPluginName);
            $paths["{$newPluginName}"] = "{$basePath}/bundles/{$newPluginName}/js";
        }

        // $paths['balloon-video-player'] = 'http://player-cdn.edusoho.net/balloon-video-player';

        return $paths;
    }

    public function getContextValue($context, $key)
    {
        $keys = explode('.', $key);
        $value = $context;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key `%s` is not in context with %s',
                        $key,
                        implode(array_keys($context), ', ')
                    )
                );
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function isFeatureEnabled($feature)
    {
        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter(
            'enabled_features'
        ) : array();

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

            if ('N/A' === $location) {
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
            return $this->trans('site.twig.extension.smarttime.future');
        }

        if (0 == $diff) {
            return $this->trans('site.twig.extension.smarttime.hardly');
        }

        if ($diff < 60) {
            return $this->trans('site.twig.extension.smarttime.previous_second', array('%diff%' => $diff));
        }

        if ($diff < 3600) {
            return $this->trans('site.twig.extension.smarttime.previous_minute', array('%diff%' => round($diff / 60)));
        }

        if ($diff < 86400) {
            return $this->trans('site.twig.extension.smarttime.previous_hour', array('%diff%' => round($diff / 3600)));
        }

        if ($diff < 2592000) {
            return $this->trans('site.twig.extension.smarttime.previous_day', array('%diff%' => round($diff / 86400)));
        }

        if ($diff < 31536000) {
            return date('m-d', $time);
        }

        return date('Y-m-d', $time);
    }

    public function remainTimeFilter($value, $timeType = '')
    {
        $remainTime = array();
        $remain = $value - time();

        if ($remain <= 0 && empty($timeType)) {
            return $remainTime['second'] = '0'.$this->trans('site.date.minute');
        }

        if ($remain <= 3600 && empty($timeType)) {
            return $remainTime['minutes'] = round($remain / 60).$this->trans('site.date.minute');
        }

        if ($remain < 86400 && empty($timeType)) {
            return $remainTime['hours'] = round($remain / 3600).$this->trans('site.date.hour');
        }

        $remainTime['day'] = round(($remain < 0 ? 0 : $remain) / 86400).$this->trans('site.date.day');

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
        $remain = $remain - 86400 * $countdown['days'];

        $countdown['hours'] = intval($remain / 3600);
        $remain = $remain - 3600 * $countdown['hours'];

        $countdown['minutes'] = intval($remain / 60);
        $remain = $remain - 60 * $countdown['minutes'];

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

        if (0 === $minutes) {
            return $seconds.$this->trans('site.date.second');
        }

        return $this->trans('site.twig.extension.time_interval.minute_second', array('%diff_minute%' => $minutes, '%diff_second%' => $seconds));
    }

    public function timeRangeFilter($start, $end)
    {
        $range = date('Y-n-d H:i', $start).' - ';

        if ($this->container->get('topxia.timemachine')->inSameDay($start, $end)) {
            $range .= date('H:i', $end);
        } else {
            $range .= date('Y年n月d日 H:i', $end);
        }

        return $range;
    }

    public function timeDiffFilter($endTime, $diffDay = 0, $startTime = '')
    {
        $endSecond = strtotime(date('Y-m-d', $endTime));

        $startSecond = empty($startTime) ? strtotime(date('Y-m-d', time())) : $startTime;

        $diffDay = round(($endSecond - $startSecond) / 86400, 0, PHP_ROUND_HALF_DOWN); // 丢弃小数点

        return $diffDay > 0 ? $diffDay : 0;
    }

    public function tagsJoinFilter($tagIds)
    {
        if (empty($tagIds) || !is_array($tagIds)) {
            return '';
        }

        $tags = $this->createService('Taxonomy:TagService')->findTagsByIds($tagIds);
        $names = ArrayToolkit::column($tags, 'name');

        return join($names, ',');
    }

    public function navigationUrlFilter($url)
    {
        $url = (string) $url;

        if (strpos($url, '://')) {
            return $url;
        }

        if (!empty($url[0]) && ('/' == $url[0])) {
            return $url;
        }

        return $this->container->get('request')->getBaseUrl().'/'.$url;
    }

    /**
     *                            P -> 省全称,     p -> 省简称
     *                            C -> 城市全称,    c -> 城市简称
     *                            D -> 区全称,     d -> 区简称.
     *
     * @param [type] $districeId [description]
     * @param string $format     格式，默认格式'P C D'
     *
     * @return [type] [description]
     */
    public function locationTextFilter($districeId, $format = 'P C D')
    {
        $text = '';
        $names = $this->createService('Taxonomy:LocationService')->getLocationFullName($districeId);

        $len = strlen($format);

        for ($i = 0; $i < $len; ++$i) {
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
        $tags = $this->createService('Taxonomy:TagService')->findTagsByIds($tags);

        foreach ($tags as $tag) {
            $url = $this->container->get('router')->generate('course_explore', array('tagId' => $tag['id']));
            $links[] = "<a href=\"{$url}\" class=\"{$class}\">{$tag['name']}</a>";
        }

        return implode(' ', $links);
    }

    public function parseFileUri($uri)
    {
        $kernel = ServiceKernel::instance();

        return $kernel->createService('Content:FileService')->parseFileUri($uri);
    }

    public function getFilePath($uri, $default = '', $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $url = $assets->getUrl('assets/img/default/'.$default);

            // $url = $request->getBaseUrl() . '/assets/img/default/' . $default;

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }

        if (false !== strpos($uri, 'http://')) {
            return $uri;
        }

        $uri = $this->parseFileUri($uri);

        if ('public' == $uri['access']) {
            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').'/'.$uri['path'];
            $url = ltrim($url, ' /');
            $url = $assets->getUrl($url);

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost().$url;
            }

            return $url;
        }
    }

    public function getDefaultPath($category, $uri = '', $size = '', $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $publicUrlpath = 'assets/img/default/';
            $url = $assets->getUrl($publicUrlpath.$size.$category);

            $defaultSetting = $this->createService('System:SettingService')->get('default', array());

            $key = 'default'.ucfirst($category);
            $fileName = $key.'FileName';

            if (array_key_exists($key, $defaultSetting) && array_key_exists($fileName, $defaultSetting)) {
                if (1 == $defaultSetting[$key]) {
                    $url = $assets->getUrl($publicUrlpath.$size.$defaultSetting[$fileName]);
                }
            } elseif (array_key_exists($key, $defaultSetting) && $defaultSetting[$key]) {
                $uri = $defaultSetting[$size.'Default'.ucfirst($category).'Uri'];
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

    public function avatarPath($user, $type = 'medium', $package = 'user')
    {
        $avatar = !empty($user[$type.'Avatar']) ? $user[$type.'Avatar'] : null;

        if (empty($avatar)) {
            $avatar = $this->getSetting('avatar.png');
        }

        return $this->getFpath($avatar, 'avatar.png', $package);
    }

    private function parseUri($uri, $absolute = false, $package = 'content')
    {
        if (false !== strpos($uri, 'http://') || false !== strpos($uri, 'https://')) {
            return $uri;
        }

        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (strpos($uri, '://')) {
            $uri = $this->parseFileUri($uri);
            $url = '';

            if ('public' == $uri['access']) {
                $url = $uri['path'];
            }
        } else {
            $url = $uri;
        }
        $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').'/'.$url;

        return $this->addHost($url, $absolute, $package);
    }

    public function getSystemDefaultPath($defaultKey, $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $defaultSetting = $this->getSetting('default', array());

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
        $assets = $this->container->get('templating.helper.assets');
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

    public function getFurl($path, $defaultKey = false, $package = 'content')
    {
        return $this->getPublicFilePath($path, $defaultKey, true, $package);
    }

    public function getFpath($path, $defaultKey = false, $package = 'content')
    {
        return $this->getPublicFilePath($path, $defaultKey, false, $package);
    }

    private function getPublicFilePath($path, $defaultKey = false, $absolute = false, $package = 'content')
    {
        $assets = $this->container->get('templating.helper.assets');

        if (empty($path)) {
            $defaultSetting = $this->getSetting('default', array());

            if ((('course.png' == $defaultKey && array_key_exists(
                            'defaultCoursePicture',
                            $defaultSetting
                        ) && 1 == $defaultSetting['defaultCoursePicture'])
                    || ('avatar.png' == $defaultKey && array_key_exists(
                            'defaultAvatar',
                            $defaultSetting
                        ) && 1 == $defaultSetting['defaultAvatar']))
                && (array_key_exists($defaultKey, $defaultSetting)
                    && $defaultSetting[$defaultKey])
            ) {
                $path = $defaultSetting[$defaultKey];

                return $this->parseUri($path, $absolute, $package);
            } else {
                return $this->addHost('/assets/img/default/'.$defaultKey, $absolute, $package);
            }
        }

        return $this->parseUri($path, $absolute, $package);
    }

    private function addHost($path, $absolute, $package = 'content')
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get($package);

        if ($cdnUrl) {
            $isSecure = $this->container->get('request')->isSecure();
            $protocal = $isSecure ? 'https:' : 'http:';
            $path = $protocal.$cdnUrl.$path;
        } elseif ($absolute) {
            $request = $this->container->get('request');
            $path = $request->getSchemeAndHttpHost().$path;
        }

        return $path;
    }

    public function basePath($package = 'content')
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get($package);

        if ($cdnUrl) {
            $isSecure = $this->container->get('request')->isSecure();
            $protocal = $isSecure ? 'https:' : 'http:';
            $path = $protocal.$cdnUrl;
        } else {
            $request = $this->container->get('request');
            $path = $request->getSchemeAndHttpHost();
        }

        return $path;
    }

    public function fileSizeFilter($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);

        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1024, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;

            if ($currentValue < 1024) {
                break;
            }
        }

        return sprintf('%.2f', $currentValue).$currentUnit;
    }

    public function numberFilter($number)
    {
        if ($number <= 1000) {
            return $number;
        }

        $currentValue = $currentUnit = null;
        $unitExps = array('千' => 3, '万' => 4, '亿' => 8);

        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(10, $exp);
            $currentUnit = $unit;
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
                return $kernel->createService('User:UserService')->getUser($id);
            case 'category':
                return $kernel->createService('Taxonomy:CategoryService')->getCategory($id);
            case 'course':
                return $kernel->createService('Course:CourseService')->getCourse($id);
            case 'file_group':
                return $kernel->createService('Content:FileService')->getFileGroup($id);
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
        $fileName = explode('.', $fileName);

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
        $need = $this->createService('Group:ThreadService')->sumGoodsCoinsByThreadId($id);

        $thread = $this->createService('Group:ThreadService')->getThread($id);

        $data = explode('[/hide]', $thread['content']);

        foreach ($data as $key => $value) {
            $value = ' '.$value;
            sscanf($value, '%[^[][hide=reply]%[^$$]', $replyContent, $replyHideContent);

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

        $bbCode = preg_replace_callback(
            '/\[image\](.*?)\[\/image\]/i',
            function ($matches) use ($ext) {
                $src = $ext->getFileUrl($matches[1]);

                return "<img src='{$src}' />";
            },
            $bbCode
        );

        $bbCode = preg_replace_callback(
            '/\[audio.*?id="(\d+)"\](.*?)\[\/audio\]/i',
            function ($matches) {
                return "<span class='audio-play-trigger' href='javascript:;' data-file-id=\"{$matches[1]}\" data-file-type=\"audio\"></span>";
            },
            $bbCode
        );

        return $bbCode;
    }

    public function scoreTextFilter($text)
    {
        $text = number_format($text, 1, '.', '');

        if ((int) $text == $text) {
            return (string) (int) $text;
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
        $stem = preg_replace_callback(
            '/\[\[.+?\]\]/',
            function ($matches) use (&$index) {
                ++$index;

                return "<span class='question-stem-fill-blank'>({$index})</span>";
            },
            $stem
        );

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

        $biz = $this->container->get('biz');

        return $biz['html_helper']->purify($html, $trusted);
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
        return $this->createService('System:SettingService')->node($name, $default);
    }

    public function getOrderPayment($order, $default = null)
    {
        $coinSettings = $this->createService('System:SettingService')->get('coin', array());

        if (!isset($coinSettings['price_type'])) {
            $coinSettings['price_type'] = 'RMB';
        }

        if (!isset($coinSettings['coin_enabled'])) {
            $coinSettings['coin_enabled'] = 0;
        }

        if (1 != $coinSettings['coin_enabled'] || 'coin' != $coinSettings['price_type']) {
            if ($order['coinAmount'] > 0 && 0 == $order['amount']) {
                $default = '余额支付';
            } else {
                $dictExtension = $this->container->get('codeages_plugin.dict_twig_extension');
                $default = $dictExtension->getDictText('payment', $order['payment']);
            }
        }

        return $default;
    }

    public function isPermitRole($classroomId, $permission, $isStudentOrAuditor = false)
    {
        $funcName = 'can'.$permission.'Classroom';

        if ($isStudentOrAuditor) {
            return $this->createService('Classroom:ClassroomService')->$funcName($classroomId, $isStudentOrAuditor);
        }

        return $this->createService('Classroom:ClassroomService')->$funcName($classroomId);
    }

    public function calculatePercent($number, $total)
    {
        if (0 == $number || 0 == $total) {
            return '0%';
        }

        if ($number >= $total) {
            return '100%';
        }

        return (int) ($number / $total * 100).'%';
    }

    public function arrayMerge($text, $content)
    {
        return array_merge($text, $content);
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

    public function getNextExecutedTime()
    {
        return $this->createService('Crontab:CrontabService')->getNextExcutedTime();
    }

    public function getUploadMaxFilesize($formated = true)
    {
        $max = FileToolkit::getMaxFilesize();

        if ($formated) {
            return FileToolkit::formatFileSize($max);
        }

        return $max;
    }

    public function isTrial()
    {
        if (file_exists($this->getParameter('kernel.root_dir').'/data/trial.lock')) {
            return true;
        }

        return false;
    }

    public function timestamp()
    {
        return time();
    }

    public function blurUserName($name)
    {
        return mb_substr($name, 0, 1, 'UTF-8').'**';
    }

    public function blur_phone_number($phoneNum)
    {
        $head = substr($phoneNum, 0, 3);
        $tail = substr($phoneNum, -4, 4);

        return $head.'****'.$tail;
    }

    public function blur_idcard_number($idcardNum)
    {
        $head = substr($idcardNum, 0, 4);
        $tail = substr($idcardNum, -2, 2);

        return $head.'************'.$tail;
    }

    public function blur_number($string)
    {
        if (SimpleValidator::email($string)) {
            $head = substr($string, 0, 1);
            $tail = substr($string, strpos($string, '@'));

            return $head.'***'.$tail;
        } elseif (SimpleValidator::mobile($string)) {
            $head = substr($string, 0, 3);
            $tail = substr($string, -4, 4);

            return $head.'****'.$tail;
        } elseif (SimpleValidator::bankCardId($string)) {
            $tail = substr($string, -4, 4);

            return '**** **** **** '.$tail;
        } elseif (SimpleValidator::idcard($string)) {
            $head = substr($string, 0, 4);
            $tail = substr($string, -2, 2);

            return $head.'************'.$tail;
        }
    }

    public function mathFormat($number, $multiplicator)
    {
        $number *= $multiplicator;

        return $number;
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    public function getPurifyAndTrimHtml($html)
    {
        $html = strip_tags($html, '');

        return preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', $html);
    }

    public function arrayColumn($array, $column)
    {
        return ArrayToolkit::column($array, $column);
    }

    private function trans($key, $parameters = array())
    {
        return $this->container->get('translator')->trans($key, $parameters);
    }

    public function mb_trim($string, $charlist = '\\\\s', $ltrim = true, $rtrim = true)
    {
        $bothEnds = $ltrim && $rtrim;

        $charClassInner = preg_replace(
            array('/[\^\-\]\\\]/S', '/\\\{4}/S'),
            array('\\\\\\0', '\\'),
            $charlist
        );

        $workHorse = '['.$charClassInner.']+';
        $ltrim && $leftPattern = '^'.$workHorse;
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

    public function wrap($object, $type)
    {
        return $this->container->get('web.wrapper')->handle($object, $type);
    }

    public function getLoginEmailAddress($email)
    {
        $dress = explode('@', $email);
        $dress = strtolower($dress[1]);
        $emailAddressMap = array(
            'gmail.com' => 'mail.google.com',
            'vip.qq.com' => 'mail.qq.com',
            'vip.163.com' => 'vip.163.com',
            'vip.sina.com' => 'mail.sina.com.cn',
            'foxmail.com' => 'mail.qq.com',
            'hotmail.com' => 'www.hotmail.com',
            '188.com' => 'www.188.com',
            '139.com' => 'mail.10086.cn',
            '126.com' => 'www.126.com',
            'yeah.net' => 'yeah.net',
        );

        if (!empty($emailAddressMap[$dress])) {
            return 'http://'.$emailAddressMap[$dress];
        }

        return 'http://mail.'.$dress;
    }

    public function getCloudSdkUrl($type)
    {
        $cdnHost = $this->getSetting('developer.cloud_sdk_cdn') ?: 'service-cdn.qiqiuyun.net';

        $paths = array(
            'player' => 'js-sdk/sdk-v1.js',
            'video' => 'js-sdk/video-player/sdk-v1.js',
            'uploader' => 'js-sdk/uploader/sdk-2.1.0.js',
            'old_uploader' => 'js-sdk/uploader/sdk-v1.js',
            'old_document' => 'js-sdk/document-player/v7/viewer.html',
            'faq' => 'js-sdk/faq/sdk-v1.js',
            'audio' => 'js-sdk/audio-player/sdk-v1.js',
        );

        if (isset($paths[$type])) {
            $path = $paths[$type];
        } else {
            $path = $type;
        }

        $timestamp = round(time() / 100);

        return '//'.trim($cdnHost, "\/").'/'.$path.'?'.$timestamp;
    }

    public function isWechatLoginBind()
    {
        $wechat = $this->isMicroMessenger();
        $loginBind = $this->getSetting('login_bind');

        return $wechat && !empty($loginBind['enabled']) && !empty($loginBind['weixinmob_enabled']);
    }

    public function isHiddenVideoHeader($isHidden = false)
    {
        return $this->getPlayerService()->isHiddenVideoHeader($isHidden);
    }

    public function canSendMessage($userId)
    {
        $user = $this->biz['user'];
        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        $toUser = $this->getUserService()->getUser($userId);
        if ($user['id'] == $toUser['id']) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $toUser['roles']) || in_array('ROLE_SUPER_ADMIN', $toUser['roles'])) {
            return true;
        }

        $messageSetting = $this->getSetting('message', array());

        if (empty($messageSetting['teacherToStudent']) && $this->isTeacher($user['roles']) && $this->isOnlyStudent($toUser['roles'])) {
            return false;
        }

        if (empty($messageSetting['studentToStudent']) && $this->isOnlyStudent($user['roles']) && $this->isOnlyStudent($toUser['roles'])) {
            return false;
        }

        if (empty($messageSetting['studentToTeacher']) && $this->isOnlyStudent($user['roles']) && $this->isTeacher($toUser['roles'])) {
            return false;
        }

        return true;
    }

    private function isTeacher($roles)
    {
        return in_array('ROLE_TEACHER', $roles);
    }

    private function isOnlyStudent($roles)
    {
        return in_array('ROLE_USER', $roles) && !in_array('ROLE_TEACHER', $roles) && !in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_SUPER_ADMIN', $roles);
    }

    public function arraysKeyConvert($arrays, $beforeKey, $afterKey)
    {
        foreach ($arrays as $key => $value) {
            if ($value == $beforeKey) {
                $arrays[$key][$afterKey] = $arrays[$key][$beforeKey];
                unset($arrays[$key][$beforeKey]);
            }
        }

        return $arrays;
    }

    public function isSystemGeneratedEmail($email)
    {
        return UserToolkit::isEmailGeneratedBySystem($email);
    }

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->createService('Player:PlayerService');
    }
}
