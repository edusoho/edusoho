<?php

namespace AppBundle\Twig;

use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\CloudFileStatusToolkit;
use AppBundle\Common\ConvertIpToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\ExtensionManager;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\NumberToolkit;
use AppBundle\Common\PluginVersionToolkit;
use AppBundle\Common\SimpleValidator;
use AppBundle\Common\UserToolkit;
use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;
use AppBundle\Component\ShareSdk\WeixinShare;
use AppBundle\Util\CategoryBuilder;
use AppBundle\Util\CdnUrl;
use AppBundle\Util\UploadToken;
use Biz\Account\Service\AccountProxyService;
use Biz\Player\Service\PlayerService;
use Biz\S2B2C\Service\FileSourceService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Topxia\Service\Common\ServiceKernel;

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

    protected $allowedCoopMode = [S2B2CFacadeService::DEALER_MODE];

    public function __construct($container, Biz $biz, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->biz = $biz;
        $this->requestStack = $requestStack;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('smart_time', [$this, 'smarttimeFilter']),
            new \Twig_SimpleFilter('date_format', [$this, 'dateformatFilter']),
            new \Twig_SimpleFilter('time_range', [$this, 'timeRangeFilter']),
            new \Twig_SimpleFilter('time_diff', [$this, 'timeDiffFilter']),
            new \Twig_SimpleFilter('remain_time', [$this, 'remainTimeFilter']),
            new \Twig_SimpleFilter('time_formatter', [$this, 'timeFormatterFilter']),
            new \Twig_SimpleFilter('location_text', [$this, 'locationTextFilter']),
            new \Twig_SimpleFilter('tags_html', [$this, 'tagsHtmlFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('file_size', [$this, 'fileSizeFilter']),
            new \Twig_SimpleFilter('plain_text', [$this, 'plainTextFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('plain_text_with_p_tag', [$this, 'plainTextWithPTagFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('sub_text', [$this, 'subTextFilter'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('duration', [$this, 'durationFilter']),
            new \Twig_SimpleFilter('duration_text', [$this, 'durationTextFilter']),
            new \Twig_SimpleFilter('tags_join', [$this, 'tagsJoinFilter']),
            new \Twig_SimpleFilter('navigation_url', [$this, 'navigationUrlFilter']),
            new \Twig_SimpleFilter('chr', [$this, 'chrFilter']),
            new \Twig_SimpleFilter('bbCode2Html', [$this, 'bbCode2HtmlFilter']),
            new \Twig_SimpleFilter('score_text', [$this, 'scoreTextFilter']),
            new \Twig_SimpleFilter('simple_template', [$this, 'simpleTemplateFilter']),
            new \Twig_SimpleFilter('fill_question_stem_text', [$this, 'fillQuestionStemTextFilter']),
            new \Twig_SimpleFilter('fill_question_stem_html', [$this, 'fillQuestionStemHtmlFilter']),
            new \Twig_SimpleFilter('get_course_id', [$this, 'getCourseidFilter']),
            new \Twig_SimpleFilter('purify_html', [$this, 'getPurifyHtml']),
            new \Twig_SimpleFilter('purify_and_trim_html', [$this, 'getPurifyAndTrimHtml']),
            new \Twig_SimpleFilter('file_type', [$this, 'getFileType']),
            new \Twig_SimpleFilter('at', [$this, 'atFilter']),
            new \Twig_SimpleFilter('copyright_less', [$this, 'removeCopyright']),
            new \Twig_SimpleFilter('array_merge', [$this, 'arrayMerge']),
            new \Twig_SimpleFilter('space2nbsp', [$this, 'spaceToNbsp']),
            new \Twig_SimpleFilter('number_to_human', [$this, 'numberFilter']),
            new \Twig_SimpleFilter('array_column', [$this, 'arrayColumn']),
            new \Twig_SimpleFilter('rename_locale', [$this, 'renameLocale']),
            new \Twig_SimpleFilter('cdn', [$this, 'cdn']),
            new \Twig_SimpleFilter('wrap', [$this, 'wrap']),
            new \Twig_SimpleFilter('convert_absolute_url', [$this, 'convertAbsoluteUrl']),
            new \Twig_SimpleFilter('url_decode', [$this, 'urlDecode']),
            new \Twig_SimpleFilter('s2b2c_file_convert', [$this, 's2b2cFileConvert']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('theme_global_script', [$this, 'getThemeGlobalScript']),
            new \Twig_SimpleFunction('file_uri_parse', [$this, 'parseFileUri']),
            // file_path 即将废弃，不要再使用
            new \Twig_SimpleFunction('file_path', [$this, 'getFilePath']),
            // default_path 即将废弃，不要再使用
            new \Twig_SimpleFunction('default_path', [$this, 'getDefaultPath']),
            // file_url 即将废弃，不要再使用
            new \Twig_SimpleFunction('file_url', [$this, 'getFileUrl']),
            // system_default_path，即将废弃，不要再使用
            new \Twig_SimpleFunction('system_default_path', [$this, 'getSystemDefaultPath']),
            new \Twig_SimpleFunction('fileurl', [$this, 'getFurl']),
            new \Twig_SimpleFunction('filepath', [$this, 'getFpath']),
            new \Twig_SimpleFunction('lazy_img', [$this, 'makeLazyImg'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('avatar_path', [$this, 'avatarPath']),
            new \Twig_SimpleFunction('object_load', [$this, 'loadObject']),
            new \Twig_SimpleFunction('setting', [$this, 'getSetting']),
            new \Twig_SimpleFunction('set_price', [$this, 'getSetPrice']),
            new \Twig_SimpleFunction('percent', [$this, 'calculatePercent']),
            new \Twig_SimpleFunction('category_choices', [$this, 'getCategoryChoices']),
            new \Twig_SimpleFunction('build_category_choices', [$this, 'buildCategoryChoices']),
            new \Twig_SimpleFunction('question_category_choices', [$this, 'getQuestionCategoryChoices']),
            new \Twig_SimpleFunction('item_category_choices', [$this, 'getItemCategoryChoices']),
            new \Twig_SimpleFunction('upload_max_filesize', [$this, 'getUploadMaxFilesize']),
            new \Twig_SimpleFunction('js_paths', [$this, 'getJsPaths']),
            new \Twig_SimpleFunction('is_plugin_installed', [$this, 'isPluginInstalled']),
            new \Twig_SimpleFunction('plugin_version', [$this, 'getPluginVersion']),
            new \Twig_SimpleFunction('version_compare', [$this, 'versionCompare']),
            new \Twig_SimpleFunction('is_exist_in_subarray_by_id', [$this, 'isExistInSubArrayById']),
            new \Twig_SimpleFunction('context_value', [$this, 'getContextValue']),
            new \Twig_SimpleFunction('is_feature_enabled', [$this, 'isFeatureEnabled']),
            new \Twig_SimpleFunction('parameter', [$this, 'getParameter']),
            new \Twig_SimpleFunction('upload_token', [$this, 'makeUploadToken']),
            new \Twig_SimpleFunction('countdown_time', [$this, 'getCountdownTime']),
            //todo covertIP 要删除
            new \Twig_SimpleFunction('convertIP', [$this, 'getConvertIP']),
            new \Twig_SimpleFunction('convert_ip', [$this, 'getConvertIP']),
            new \Twig_SimpleFunction('isHide', [$this, 'isHideThread']),
            new \Twig_SimpleFunction('user_coin_amount', [$this, 'userCoinAmount']),

            new \Twig_SimpleFunction('user_balance', [$this, 'getBalance']),

            new \Twig_SimpleFunction('blur_user_name', [$this, 'blurUserName']),
            new \Twig_SimpleFunction('blur_phone_number', [$this, 'blur_phone_number']),
            new \Twig_SimpleFunction('blur_idcard_number', [$this, 'blur_idcard_number']),
            new \Twig_SimpleFunction('blur_number', [$this, 'blur_number']),
            new \Twig_SimpleFunction('sub_str', [$this, 'subStr']),
            new \Twig_SimpleFunction('load_script', [$this, 'loadScript']),
            new \Twig_SimpleFunction('export_scripts', [$this, 'exportScripts']),
            new \Twig_SimpleFunction('order_payment', [$this, 'getOrderPayment']),
            new \Twig_SimpleFunction('classroom_permit', [$this, 'isPermitRole']),
            new \Twig_SimpleFunction('crontab_next_executed_time', [$this, 'getNextExecutedTime']),
            new \Twig_SimpleFunction('finger_print', [$this, 'getFingerprint']),
            new \Twig_SimpleFunction('get_parameters_from_url', [$this, 'getParametersFromUrl']),
            new \Twig_SimpleFunction('is_trial', [$this, 'isTrial']),
            new \Twig_SimpleFunction('timestamp', [$this, 'timestamp']),
            new \Twig_SimpleFunction('get_user_vip_level', [$this, 'getUserVipLevel']),
            new \Twig_SimpleFunction('is_without_network', [$this, 'isWithoutNetwork']),
            new \Twig_SimpleFunction('get_admin_roles', [$this, 'getAdminRoles']),
            new \Twig_SimpleFunction('render_notification', [$this, 'renderNotification']),
            new \Twig_SimpleFunction('route_exsit', [$this, 'routeExists']),
            new \Twig_SimpleFunction('is_micro_messenger', [$this, 'isMicroMessenger']),
            new \Twig_SimpleFunction('wx_js_sdk_config', [$this, 'weixinConfig']),
            new \Twig_SimpleFunction('plugin_update_notify', [$this, 'pluginUpdateNotify']),
            new \Twig_SimpleFunction('tag_equal', [$this, 'tagEqual']),
            new \Twig_SimpleFunction('array_index', [$this, 'arrayIndex']),
            new \Twig_SimpleFunction('cdn', [$this, 'getCdn']),
            new \Twig_SimpleFunction('is_show_mobile_page', [$this, 'isShowMobilePage']),
            new \Twig_SimpleFunction('is_mobile_client', [$this, 'isMobileClient']),
            new \Twig_SimpleFunction('is_ios_client', [$this, 'isIOSClient']),
            new \Twig_SimpleFunction('is_ES_copyright', [$this, 'isESCopyright']),
            new \Twig_SimpleFunction('get_classroom_name', [$this, 'getClassroomName']),
            new \Twig_SimpleFunction('pop_reward_point_notify', [$this, 'popRewardPointNotify']),
            new \Twig_SimpleFunction('array_filter', [$this, 'arrayFilter']),
            new \Twig_SimpleFunction('base_path', [$this, 'basePath']),
            new \Twig_SimpleFunction('get_login_email_address', [$this, 'getLoginEmailAddress']),
            new \Twig_SimpleFunction('cloud_sdk_url', [$this, 'getCloudSdkUrl']),
            new \Twig_SimpleFunction('math_format', [$this, 'mathFormat']),
            new \Twig_SimpleFunction('parse_user_agent', [$this, 'parseUserAgent']),
            new \Twig_SimpleFunction('wechat_login_bind_enabled', [$this, 'isWechatLoginBind']),
            new \Twig_SimpleFunction('can_send_message', [$this, 'canSendMessage']),
            new \Twig_SimpleFunction('is_hidden_video_header', [$this, 'isHiddenVideoHeader']),
            new \Twig_SimpleFunction('arrays_key_convert', [$this, 'arraysKeyConvert']),
            new \Twig_SimpleFunction('is_system_generated_email', [$this, 'isSystemGeneratedEmail']),
            new \Twig_SimpleFunction('get_transcode_error_message_key', [$this, 'getTranscodeErrorMessageKeyByCode']),
            new \Twig_SimpleFunction('uniqid', [$this, 'uniqid']),
            new \Twig_SimpleFunction('get_days', [$this, 'getDays']),
            new \Twig_SimpleFunction('is_question_lack', [$this, 'isQuestionLack']),
            new \Twig_SimpleFunction('is_s2b2c_enabled', [$this, 'isS2B2CEnabled']),
            new \Twig_SimpleFunction('s2b2c_has_behaviour_permission', [$this, 's2b2cHasBehaviourPermission']),
            new \Twig_SimpleFunction('make_local_media_file_token', [$this, 'makeLocalMediaFileToken']),
        ];
    }

    public function makeLocalMediaFileToken($file)
    {
        $token = $this->makeToken('local.media', $file['id']);

        return $token['token'];
    }

    /**
     * @return bool
     *              s2b2c.config由S2B2CProvider初始化，初始化后存在与否决定了是否开启了s2b2c
     */
    public function isS2B2CEnabled()
    {
        return !empty($this->biz['s2b2c.config']['enabled']);
    }

    /**
     * @param $action
     *
     * @return bool
     *              未定义的action 默认为有权限，true
     */
    public function s2b2cHasBehaviourPermission($action)
    {
        $behaviourPermissions = $this->getS2B2CFacadeService()->getBehaviourPermissions();

        return isset($behaviourPermissions[$action]) ? $behaviourPermissions[$action] : true;
    }

    public function urlDecode($url)
    {
        return !empty($url) ? urldecode($url) : '';
    }

    public function s2b2cFileConvert($file)
    {
        return $this->getS2b2cFileSourceService()->getFullFileInfo($file);
    }

    public function getDays($days)
    {
        if (7 == count($days)) {
            return $this->trans('course.remind.each_week');
        }

        $result = '';
        foreach ($days as $day) {
            switch ($day) {
                case 'Mon':
                    $result = $result.''.$this->trans('course.remind.mon').' 、';
                    break;
                case 'Tue':
                    $result = $result.''.$this->trans('course.remind.tue').' 、';
                    break;
                case 'Wed':
                    $result = $result.''.$this->trans('course.remind.wed').' 、';
                    break;
                case 'Thu':
                    $result = $result.''.$this->trans('course.remind.thu').' 、';
                    break;
                case 'Fri':
                    $result = $result.''.$this->trans('course.remind.fri').' 、';
                    break;
                case 'Sat':
                    $result = $result.''.$this->trans('course.remind.sat').' 、';
                    break;
                case 'Sun':
                    $result = $result.''.$this->trans('course.remind.sun').' 、';
                    break;
                default:
                    break;
            }
        }

        return rtrim($result, '、');
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

        return [
            'device' => $deviceDetector->getDevice(),
            'client' => $deviceDetector->getClient(),
            'os' => $deviceDetector->getOs(),
        ];
    }

    public function arrayFilter($data, $filterName)
    {
        if (empty($data) || !is_array($data)) {
            return [];
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
        $wapSetting = $this->getSetting('wap', ['version' => 0]);

        if (empty($wapSetting['version'])) {
            return false;
        }

        $pcVersion = $this->requestStack->getMasterRequest()->cookies->get('PCVersion', 0);
        if ($pcVersion) {
            return false;
        }

        return DeviceToolkit::isMobileClient();
    }

    public function isMobileClient()
    {
        return DeviceToolkit::isMobileClient();
    }

    public function isIOSClient()
    {
        return DeviceToolkit::isIOSClient();
    }

    public function isESCopyright()
    {
        $copyright = $this->getSetting('copyright');
        $request = $this->requestStack->getMasterRequest();

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
            return [];
        }

        return ArrayToolkit::index($array, $key);
    }

    public function timeFormatterFilter($time)
    {
        if ($time <= 60) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => 0]);
        }

        if ($time <= 3600) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => round($time / 60)]);
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', ['%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)]);
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
            []
        );

        return $notifies;
    }

    public function getAdminRoles()
    {
        return $this->createService('Role:RoleService')->searchRoles([], 'created', 0, 1000);
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
            $config = ['key' => $key, 'secret' => $secret];
            $weixinshare = new WeixinShare($config);
            $token = $weixinshare->getJsApiTicket();
            if (empty($token)) {
                return [];
            }

            $jsApiTicket = $this->createService('User:TokenService')->makeToken(
                'jsapi.ticket',
                ['data' => $token, 'duration' => $token['expires_in']]
            );
        }

        $config = [
            'appId' => $key,
            'timestamp' => time(),
            'nonceStr' => uniqid($prefix = 'edusoho'),
            'jsApiList' => ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQZone', 'onMenuShareQQ'],
        ];

        $jsapi_ticket = $jsApiTicket['data']['ticket'];
        $url = empty($url) ? $this->requestStack->getMasterRequest()->getUri() : $url;
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
                $parameters = [];

                foreach ($parameter as $key => $value) {
                    $parameters[$key] = explode('=', $value);
                }
            } else {
                $parameter = explode('=', $BaseUrl['query']);
                $parameters = [];
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
        return false !== strpos($this->requestStack->getMasterRequest()->headers->get('User-Agent'), 'MicroMessenger');
    }

    public function renameLocale($locale)
    {
        $locale = strtolower($locale);
        $locale = str_replace('_', '-', $locale);

        return 'zh-cn' == $locale ? '' : '-'.$locale;
    }

    public function getFingerprint()
    {
        $user = $this->biz['user'];

        if (!$user->isLogin()) {
            return '';
        }

        $user = $this->getUserService()->getUser($user['id']);

        // @todo 如果配置用户的关键信息，这个方法存在信息泄漏风险，更换新播放器后解决这个问题。
        $pattern = $this->getSetting('magic.video_fingerprint');
        $opacity = $this->getSetting('storage.video_fingerprint_opacity', 1);

        if ($pattern) {
            $fingerprint = $this->parsePattern($pattern, $user);
        } else {
            $request = $this->requestStack->getMasterRequest();
            $host = $request->getHttpHost();
            $fingerprint = "<span style=\"opacity:{$opacity};\"> {$host} {$user['nickname']} </span>";
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

        $condition = [
            'user_id' => $userId,
            'type' => $type,
            'amount_type' => 'coin',
        ];
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

    /**
     * @return UserService
     */
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
            $basePath = $this->requestStack->getMasterRequest()->getBasePath();
        }

        $theme = $this->getSetting('theme.uri', 'default');

        $plugins = $this->container->get('kernel')->getPlugins();
        $names = [];
        $newPluginNames = [];

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

        $paths = [
            'common' => 'common',
            'theme' => "{$basePath}/themes/{$theme}/js",
        ];

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
                throw new \InvalidArgumentException(sprintf('Key `%s` is not in context with %s', $key, implode(array_keys($context), ', ')));
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function isFeatureEnabled($feature)
    {
        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter(
            'enabled_features'
        ) : [];

        return in_array($feature, $features);
    }

    public function getParameter($name, $default = null)
    {
        if (!$this->container->hasParameter($name)) {
            return $default;
        }

        return $this->container->getParameter($name);
    }

    public function makeUploadToken($group, $type = 'image', $duration = 18000)
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
            return $this->trans('site.twig.extension.smarttime.previous_second', ['%diff%' => $diff]);
        }

        if ($diff < 3600) {
            return $this->trans('site.twig.extension.smarttime.previous_minute', ['%diff%' => round($diff / 60)]);
        }

        if ($diff < 86400) {
            return $this->trans('site.twig.extension.smarttime.previous_hour', ['%diff%' => round($diff / 3600)]);
        }

        if ($diff < 2592000) {
            return $this->trans('site.twig.extension.smarttime.previous_day', ['%diff%' => round($diff / 86400)]);
        }

        if ($diff < 31536000) {
            return date('m-d', $time);
        }

        return date('Y-m-d', $time);
    }

    public function remainTimeFilter($value, $timeType = '')
    {
        $remainTime = [];
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
        $countdown = ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];

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

        return $this->trans('site.twig.extension.time_interval.minute_second', ['%diff_minute%' => $minutes, '%diff_second%' => $seconds]);
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

        return $this->requestStack->getMasterRequest()->getBaseUrl().'/'.$url;
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
        $links = [];
        $tags = $this->createService('Taxonomy:TagService')->findTagsByIds($tags);

        foreach ($tags as $tag) {
            $url = $this->container->get('router')->generate('course_explore', ['tagId' => $tag['id']]);
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
        $assets = $this->container->get('assets.packages');
        $request = $this->requestStack->getMasterRequest();

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
        $assets = $this->container->get('assets.packages');
        $request = $this->requestStack->getMasterRequest();

        if (empty($uri)) {
            $publicUrlpath = 'assets/img/default/';
            $url = $assets->getUrl($publicUrlpath.$size.$category);

            $defaultSetting = $this->createService('System:SettingService')->get('default', []);

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

        $assets = $this->container->get('assets.packages');
        $request = $this->requestStack->getMasterRequest();

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
        $assets = $this->container->get('assets.packages');
        $defaultSetting = $this->getSetting('default', []);

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
        $imgpath = $path = $this->container->get('assets.packages')->getUrl('assets/img/default/'.$img);

        return sprintf('<img src="%s" alt="%s" class="%s" data-echo="%s" />', $imgpath, $alt, $class, $src);
    }

    public function loadScript($js)
    {
        $js = is_array($js) ? $js : [$js];

        if ($this->pageScripts) {
            $this->pageScripts = array_merge($this->pageScripts, $js);
        } else {
            $this->pageScripts = $js;
        }
    }

    public function exportScripts()
    {
        if (empty($this->pageScripts)) {
            $this->pageScripts = [];
        }

        return array_values(array_unique($this->pageScripts));
    }

    public function getFileUrl($uri, $default = '', $absolute = false)
    {
        $assets = $this->container->get('assets.packages');
        $request = $this->requestStack->getMasterRequest();

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
        $assets = $this->container->get('assets.packages');

        if (empty($path)) {
            $defaultSetting = $this->getSetting('default', []);

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
            $isSecure = $this->requestStack->getMasterRequest()->isSecure();
            $protocal = $isSecure ? 'https:' : 'http:';
            $path = $protocal.$cdnUrl.$path;
        } elseif ($absolute) {
            $request = $this->requestStack->getMasterRequest();
            $path = $request->getSchemeAndHttpHost().$path;
        }

        return $path;
    }

    public function basePath($package = 'content')
    {
        $cdn = new CdnUrl();
        $cdnUrl = $cdn->get($package);

        if ($cdnUrl) {
            $isSecure = $this->requestStack->getMasterRequest()->isSecure();
            $protocal = $isSecure ? 'https:' : 'http:';
            $path = $protocal.$cdnUrl;
        } else {
            $request = $this->requestStack->getMasterRequest();
            $path = $request->getSchemeAndHttpHost();
        }

        return $path;
    }

    public function fileSizeFilter($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3];

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
        $unitExps = ['千' => 3, '万' => 4, '亿' => 8];

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

    public function plainTextWithPTagFilter($text)
    {
        $text = strip_tags($text, '<p>');

        $text = str_replace(["\n", "\r", "\t"], '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);

        return $text;
    }

    public function plainTextFilter($text, $length = null)
    {
        $text = strip_tags($text);

        $text = str_replace(["\n", "\r", "\t"], '', $text);
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

        $text = str_replace(["\n", "\r", "\t"], '', $text);
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
        return preg_replace('/\[\[\]\]/', '____', preg_replace('/\[\[.+?\]\]/', '____', $stem));
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

    public function atFilter($text, $ats = [])
    {
        if (empty($ats) || !is_array($ats)) {
            return $text;
        }

        $router = $this->container->get('router');

        foreach ($ats as $nickname => $userId) {
            $path = $router->generate('user_show', ['id' => $userId]);
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
        $coinSettings = $this->createService('System:SettingService')->get('coin', []);

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

    public function buildCategoryChoices($categories, $indent = '　')
    {
        $builder = new CategoryBuilder();
        $builder->build($categories);
        $builder->setIndent($indent);

        return $builder->convertToChoices();
    }

    public function getCategoryChoices($groupCode, $indent = '　')
    {
        $builder = new CategoryBuilder();
        $builder->buildForTaxonomy($groupCode);
        $builder->setIndent($indent);

        return $builder->convertToChoices();
    }

    public function getQuestionCategoryChoices($bankId, $indent = '　')
    {
        $builder = new CategoryBuilder();
        $builder->buildForQuestion($bankId);
        $builder->setIndent($indent);

        return $builder->convertToChoices();
    }

    public function getItemCategoryChoices($itemBankId, $indent = '　')
    {
        $builder = new CategoryBuilder();
        $builder->buildForItem($itemBankId);
        $builder->setIndent($indent);

        return $builder->convertToChoices();
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

    private function trans($key, $parameters = [])
    {
        return $this->container->get('translator')->trans($key, $parameters);
    }

    public function mb_trim($string, $charlist = '\\\\s', $ltrim = true, $rtrim = true)
    {
        $bothEnds = $ltrim && $rtrim;

        $charClassInner = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S'],
            ['\\\\\\0', '\\'],
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
        $emailAddressMap = [
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
        ];

        if (!empty($emailAddressMap[$dress])) {
            return 'http://'.$emailAddressMap[$dress];
        }

        return 'http://mail.'.$dress;
    }

    public function getCloudSdkUrl($type)
    {
        return $this->getResourceFacadeService()->getFrontPlaySDKPathByType($type);
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

        $toUser = $this->getUserService()->getUser($userId);
        if (1 == $toUser['destroyed'] || 1 == $user['destroyed']) {
            return false;
        }

        if ($user['id'] == $toUser['id']) {
            return false;
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $toUser['roles']) || in_array('ROLE_SUPER_ADMIN', $toUser['roles'])) {
            return true;
        }

        $messageSetting = $this->getSetting('message', []);

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

    public function getTranscodeErrorMessageKeyByCode($code)
    {
        return CloudFileStatusToolkit::getTranscodeErrorMessageKeyByCode($code);
    }

    public function uniqid()
    {
        return MathToolkit::uniqid();
    }

    public function isQuestionLack($activity)
    {
        try {
            $this->getAssessmentService()->drawItems($activity['ext']['drawCondition']['range'], [$activity['ext']['drawCondition']['section']]);

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function canModifySiteName()
    {
        $merchantSetting = $this->getSettingService()->get('merchant_setting');

        if (empty($merchantSetting['coop_mode']) || empty($merchantSetting['auth_node'])) {
            return true;
        }

        return in_array($merchantSetting['coop_mode'], $this->allowedCoopMode) || !empty($merchantSetting['auth_node']['title']);
    }

    public function canModifySiteUrl()
    {
        $merchantSetting = $this->getSettingService()->get('merchant_setting');

        if (empty($merchantSetting['coop_mode']) || empty($merchantSetting['auth_node'])) {
            return true;
        }

        return empty($merchantSetting['coop_mode']) ? true : in_array($merchantSetting['coop_mode'], $this->allowedCoopMode);
    }

    public function canModifySiteLogo()
    {
        $merchantSetting = $this->getSettingService()->get('merchant_setting');

        if (empty($merchantSetting['coop_mode']) || empty($merchantSetting['auth_node'])) {
            return true;
        }

        return in_array($merchantSetting['coop_mode'], $this->allowedCoopMode) || !empty($merchantSetting['auth_node']['logo']);
    }

    public function canModifySiteFavicon()
    {
        $merchantSetting = $this->getSettingService()->get('merchant_setting');

        if (empty($merchantSetting['coop_mode']) || empty($merchantSetting['auth_node'])) {
            return true;
        }

        return in_array($merchantSetting['coop_mode'], $this->allowedCoopMode) || !empty($merchantSetting['auth_node']['favicon']);
    }

    protected function makeToken($type, $fileId, $context = [])
    {
        $fields = [
            'data' => [
                'id' => $fileId,
            ],
            'times' => 10,
            'duration' => 3600,
            'userId' => $this->biz['user']['id'],
        ];

        if (isset($context['watchTimeLimit'])) {
            $fields['data']['watchTimeLimit'] = $context['watchTimeLimit'];
        }

        if (isset($context['hideBeginning'])) {
            $fields['data']['hideBeginning'] = $context['hideBeginning'];
        }

        return $this->getTokenService()->makeToken($type, $fields);
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->createService('Player:PlayerService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestPaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return FileSourceService
     */
    protected function getS2b2cFileSourceService()
    {
        return $this->createService('S2B2C:FileSourceService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    protected function getResourceFacadeService()
    {
        return $this->createService('CloudPlatform:ResourceFacadeService');
    }
}
