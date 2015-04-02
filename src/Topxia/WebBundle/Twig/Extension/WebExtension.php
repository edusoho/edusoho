<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\NumberToolkit;
use Topxia\Common\ConvertIpToolkit;
use Topxia\Service\Util\HTMLPurifierFactory;
use Topxia\WebBundle\Util\UploadToken;

class WebExtension extends \Twig_Extension
{
    protected $container;

    protected $pageScripts;

    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getFilters ()
    {
        return array(
            'smart_time' => new \Twig_Filter_Method($this, 'smarttimeFilter') ,
            'data_format' => new \Twig_Filter_Method($this, 'dataformatFilter') ,
            'time_range' => new \Twig_Filter_Method($this, 'timeRangeFilter'),
            'remain_time' => new \Twig_Filter_Method($this, 'remainTimeFilter'),
            'location_text' => new \Twig_Filter_Method($this, 'locationTextFilter'),
            'tags_html' => new \Twig_Filter_Method($this, 'tagsHtmlFilter', array('is_safe' => array('html'))),
            'file_size'  => new \Twig_Filter_Method($this, 'fileSizeFilter'),
            'plain_text' => new \Twig_Filter_Method($this, 'plainTextFilter', array('is_safe' => array('html'))),
            'sub_text' => new \Twig_Filter_Method($this, 'subTextFilter', array('is_safe' => array('html'))),
            'duration'  => new \Twig_Filter_Method($this, 'durationFilter'),
            'tags_join' => new \Twig_Filter_Method($this, 'tagsJoinFilter'),
            'navigation_url' => new \Twig_Filter_Method($this, 'navigationUrlFilter'),
            'chr' => new \Twig_Filter_Method($this, 'chrFilter'),
            'bbCode2Html' => new \Twig_Filter_Method($this, 'bbCode2HtmlFilter'),
            'score_text' => new \Twig_Filter_Method($this, 'scoreTextFilter'),
            'fill_question_stem_text' =>new \Twig_Filter_Method($this, 'fillQuestionStemTextFilter'),
            'fill_question_stem_html' =>new \Twig_Filter_Method($this, 'fillQuestionStemHtmlFilter'),
            'get_course_id' => new \Twig_Filter_Method($this, 'getCourseidFilter'),
            'purify_html' => new \Twig_Filter_Method($this, 'getPurifyHtml'),
            'file_type' => new \Twig_Filter_Method($this, 'getFileType'),
            'at' => new \Twig_Filter_Method($this, 'atFilter'),
            'copyright_less' => new \Twig_Filter_Method($this, 'removeCopyright'),
        );
    }

    public function getFunctions()
    {
        return array(
            'theme_global_script' => new \Twig_Function_Method($this, 'getThemeGlobalScript') ,
            'file_uri_parse'  => new \Twig_Function_Method($this, 'parseFileUri'),
            // file_path即将废弃，不要再使用
            'file_path'  => new \Twig_Function_Method($this, 'getFilePath'),
            'default_path'  => new \Twig_Function_Method($this, 'getDefaultPath'),
            'system_default_path' => new \Twig_Function_Method($this,'getSystemDefaultPath'),
            'file_url'  => new \Twig_Function_Method($this, 'getFileUrl'),
            'object_load'  => new \Twig_Function_Method($this, 'loadObject'),
            'setting' => new \Twig_Function_Method($this, 'getSetting') ,
            'set_price' => new \Twig_Function_Method($this, 'getSetPrice') ,
            'percent' => new \Twig_Function_Method($this, 'calculatePercent') ,
            'category_choices' => new \Twig_Function_Method($this, 'getCategoryChoices') ,
            'dict' => new \Twig_Function_Method($this, 'getDict') ,
            'dict_text' => new \Twig_Function_Method($this, 'getDictText', array('is_safe' => array('html'))) ,
            'upload_max_filesize' => new \Twig_Function_Method($this, 'getUploadMaxFilesize') ,
            'js_paths' => new \Twig_Function_Method($this, 'getJsPaths'),
            'is_plugin_installed' => new \Twig_Function_Method($this, 'isPluginInstaled'),
            'plugin_version' => new \Twig_Function_Method($this, 'getPluginVersion'),
            'version_compare' => new \Twig_Function_Method($this, 'versionCompare'),
            'is_exist_in_subarray_by_id' => new \Twig_Function_Method($this, 'isExistInSubArrayById'),
            'context_value' => new \Twig_Function_Method($this, 'getContextValue') ,
            'is_feature_enabled' => new \Twig_Function_Method($this, 'isFeatureEnabled') ,
            'parameter' => new \Twig_Function_Method($this, 'getParameter') ,
            'upload_token' => new \Twig_Function_Method($this, 'makeUpoadToken') ,
            'free_limit_type' => new \Twig_Function_Method($this, 'getFreeLimitType') ,
            'countdown_time' =>  new \Twig_Function_Method($this, 'getCountdownTime'),
            'convertIP' => new \Twig_Function_Method($this, 'getConvertIP'),
            'isHide'=>new \Twig_Function_Method($this, 'isHideThread'),
            'userOutCash'=>new \Twig_Function_Method($this, 'getOutCash'),
            'userInCash'=>new \Twig_Function_Method($this, 'getInCash'),
            'userAccount'=>new \Twig_Function_Method($this, 'getAccount'),
            'getUserNickNameById' => new \Twig_Function_Method($this, 'getUserNickNameById'),
            'blur_phone_number' => new \Twig_Function_Method($this, 'blur_phone_number'),
            'sub_str' => new \Twig_Function_Method($this, 'subStr'),
            'load_script' => new \Twig_Function_Method($this, 'loadScript'),
            'export_scripts' => new \Twig_Function_Method($this, 'exportScripts'), 
            'getClassroomsByCourseId' => new \Twig_Function_Method($this, 'getClassroomsByCourseId'),
            'order_payment' => new \Twig_Function_Method($this, 'getOrderPayment') ,
        );
    }

    public function subStr($text, $start, $length)
    {
        $text = trim($text);

        $length = (int) $length;
        if ( ($length > 0) && (mb_strlen($text) > $length) )  {
            $text = mb_substr($text, $start, $length, 'UTF-8');
        }
        return $text;
    }

    public function getOutCash($userId,$timeType="oneWeek")
    {   
        $time=$this->filterTime($timeType);
        $condition=array(
            'userId'=>$userId,
            'type'=>"outflow",
            'cashType'=>'Coin',
            'startTime'=>$time,
            );

        return ServiceKernel::instance()->createService('Cash.CashService')->analysisAmount($condition);
    }

    public function getInCash($userId,$timeType="oneWeek")
    {   
        $time=$this->filterTime($timeType);
        $condition=array(
            'userId'=>$userId,
            'type'=>"inflow",
            'cashType'=>'Coin',
            'startTime'=>$time,
            );
        return ServiceKernel::instance()->createService('Cash.CashService')->analysisAmount($condition);
    }

    public function getAccount($userId)
    {   
        return ServiceKernel::instance()->createService('Cash.CashAccountService')->getAccountByUserId($userId);
    }

    private function filterTime($type)
    {   
        $time=0;
        switch ($type) {
                case 'oneWeek':
                    $time=time()-7*3600*24;
                    break;
                case 'oneMonth':
                    $time=time()-30*3600*24;
                    break;                
                case 'threeMonths':
                    $time=time()-90*3600*24;
                    break;
                default:
                    break;
        }

        return $time;
    }

    public function getUserNickNameById($userId)
    {
        $user = $this->getUserById($userId);
        return $user['nickname'];
    }
    
    public function getClassroomsByCourseId($courseId)
    {   
        $classrooms=array();
        $classroomIds=ArrayToolkit::column(ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->findClassroomsByCourseId($courseId),'classroomId');
        foreach ($classroomIds as $key => $value) {
            $classrooms[$value]=ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->getClassroom($value);
        }

        return $classrooms;
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
        $theme = $this->getSetting('theme.uri', 'default');
        $filePath = realpath($this->container->getParameter('kernel.root_dir') . "/../web/themes/{$theme}/js/global-script.js");
        if ($filePath) {
            return 'theme/global-script';
        }
        return '';
    }

    public function isPluginInstaled($name)
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
            if ( strtolower($plugin['code']) == strtolower($name) ) {
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
        $theme = $this->getSetting('theme.uri', 'default');

        $plugins = $this->container->get('kernel')->getPlugins();
        $names = array();
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

        $paths = array(
            'common' => 'common',
            'theme' => "{$basePath}/themes/{$theme}/js"
        );

        foreach ($names as $name) {
            $name = strtolower($name);
            $paths["{$name}bundle"] = "{$basePath}/bundles/{$name}/js";
        }

        return $paths;
    }

    public function getContextValue($context, $key)
    {
        $keys = explode('.', $key);
        $value = $context;
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                throw new \InvalidArgumentException(sprintf("Key `%s` is not in context with %s", $key, implode(array_keys($context), ', ')) );
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

    public function makeUpoadToken($group, $type = 'image' , $duration = 18000)
    {
        $maker = new UploadToken();
        return $maker->make($group, $type, $duration);
    }

    public function getConvertIP($IP)
    {
        
        if(!empty($IP)){
                $location = ConvertIpToolkit::convertIp($IP);
            if ($location === 'INNA') 
                return '未知区域';
            return $location;
        }
        return '';
    }


    public function dataformatFilter ($time) {
        if (empty($time)) {
            return ;
        }
        return date('Y-m-d H:i',$time);
    }

    public function smarttimeFilter ($time) {
        $diff = time() - $time;
        if ($diff < 0) {
            return '未来';
        }

        if ($diff == 0) {
            return '刚刚';
        }

        if ($diff < 60) {
            return $diff . '秒前';
        }

        if ($diff < 3600) {
            return round($diff / 60) . '分钟前';
        }

        if ($diff < 86400) {
            return round($diff / 3600) . '小时前';
        }

        if ($diff < 2592000) {
            return round($diff / 86400) . '天前';
        }

        if ($diff < 31536000) {
            return date('m-d', $time);
        }

        return date('Y-m-d', $time);
    }

    public function remainTimeFilter($value)
    {
        $remain = $value - time();

        if ($remain <= 0) {
            return '0分钟';
        }

        if ($remain <= 3600) {
            return round($remain / 60) . '分钟';
        }

        if ($remain < 86400) {
            return round($remain / 3600) . '小时';
        }

        return round($remain / 86400) . '天';
    }

    public function getCountdownTime($value)
    {
        $countdown = array('days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0);

        $remain = $value - time();
        if ($remain <=0 ) {
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
        return sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
    }

    public function timeRangeFilter($start, $end)
    {
        $range = date('Y年n月d日 H:i', $start) . ' - ';

        if ($this->container->get('topxia.timemachine')->inSameDay($start, $end)) {
            $range .= date('H:i', $end);
        } else {
            $range .= date('Y年n月d日 H:i', $end);
        }

        return $range;
    }

    public function tagsJoinFilter($tagIds)
    {
        if (empty($tagIds) or !is_array($tagIds)) {
            return '';
        }

        $tags = ServiceKernel::instance()->createService('Taxonomy.TagService')->findTagsByIds($tagIds);
        $names = ArrayToolkit::column($tags, 'name');

        return join($names, ',');
    }

    public function navigationUrlFilter($url)
    {
        $url = (string) $url;
        if (strpos($url, '://')) {
            return $url;
        }

        if (!empty($url[0]) and ($url[0] == '/')) {
            return $url;
        }

        return $this->container->get('request')->getBaseUrl() . '/' . $url;
    }

    /**
     * @param  [type] $districeId [description]
     * @param  string $format     格式，默认格式'P C D'。
     *                            P -> 省全称,     p -> 省简称
     *                            C -> 城市全称,    c -> 城市简称
     *                            D -> 区全称,     d -> 区简称
     * @return [type]             [description]
     */
    public function locationTextFilter($districeId, $format = 'P C D')
    {
        $text = '';
        $names = ServiceKernel::instance()->createService('Taxonomy.LocationService')->getLocationFullName($districeId);


        $len = strlen($format);
        for ($i=0; $i < $len; $i++) {
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
        $tags = ServiceKernel::instance()->createService('Taxonomy.TagService')->findTagsByIds($tags);
        foreach ($tags as $tag) {
            $url = $this->container->get('router')->generate('course_explore', array('tagId' => $tag['id']));
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
        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');
        if (empty($uri)) {
            $url = $assets->getUrl('assets/img/default/' . $default);
            // $url = $request->getBaseUrl() . '/assets/img/default/' . $default;
            if ($absolute) {
                $url = $request->getSchemeAndHttpHost() . $url;
            }
            return $url;
        }
        $uri = $this->parseFileUri($uri);
        if ($uri['access'] == 'public') {
            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /') . '/' . $uri['path'];
            $url = ltrim($url, ' /');
            $url = $assets->getUrl($url);

            if ($absolute) {
                $url = $request->getSchemeAndHttpHost() . $url;
            }

            return $url;
        } else {

        }
    }

    public function getDefaultPath($category, $uri="", $size = '', $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        $cdn = ServiceKernel::instance()->createService('System.SettingService')->get('cdn',array());
        $cdnUrl = (empty($cdn['enabled'])) ? '' : rtrim($cdn['url'], " \/");
        
        if (empty($uri)) {
            $publicUrlpath = 'assets/img/default/';
            $url = $assets->getUrl($publicUrlpath . $size . $category);

            $defaultSetting = ServiceKernel::instance()->createService('System.SettingService')->get('default',array());

            $key = 'default'.ucfirst($category);
            $fileName = $key.'FileName';
            if (array_key_exists($key, $defaultSetting) && array_key_exists($fileName, $defaultSetting)){
                if ($defaultSetting[$key] == 1) {
                    $url = $assets->getUrl($publicUrlpath . $size .$defaultSetting[$fileName]);
                    return $url;
                } else {
                    if ($absolute) {
                        $url = $request->getSchemeAndHttpHost() . $url;
                    }
                   return $url;
                }
            } else {
                return $url;
            }
        }

        $uri = $this->parseFileUri($uri);
        if ($uri['access'] == 'public') {
            
            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /') . '/' . $uri['path'];
            $url = ltrim($url, ' /');
            $url = $assets->getUrl($url);

            if ($cdnUrl) {
                $url = $cdnUrl . $url;
            } else {
                if ($absolute) {
                    $url = $request->getSchemeAndHttpHost() . $url;
                }
            }

            return $url;
        }else{

        }

    }

    public function getSystemDefaultPath($category,$systemDefault = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $publicUrlpath = 'assets/img/default/';

        $defaultSetting = ServiceKernel::instance()->createService('System.SettingService')->get('default',array());

        if($systemDefault && isset($defaultSetting)){
            $fileName = 'default'.ucfirst($category).'FileName';
            if (array_key_exists($fileName, $defaultSetting)) {
                $url = $assets->getUrl($publicUrlpath .$defaultSetting[$fileName]);
            } else {
            $url = $assets->getUrl($publicUrlpath . $category);
            }
        } else {
            $url = $assets->getUrl($publicUrlpath . $category);
        }

        return $url;
    }

    public  function loadScript($js)
    {
        $js = is_array($js) ? $js : array($js);
        
        if($this->pageScripts) {
            $this->pageScripts = array_merge($this->pageScripts, $js);
        } else {
            $this->pageScripts = $js;
        }
    }

    public function exportScripts()
    {
        return $this->pageScripts;
    }

    public function getFileUrl($uri, $default = '', $absolute = false)
    {
        $assets = $this->container->get('templating.helper.assets');
        $request = $this->container->get('request');

        if (empty($uri)) {
            $url = $assets->getUrl('assets/img/default/' . $default);
            if ($absolute) {
                $url = $request->getSchemeAndHttpHost() . $url;
            }
            return $url;
        }

        $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /') . '/' . $uri;
        $url = ltrim($url, ' /');
        $url = $assets->getUrl($url);

        if ($absolute) {
            $url = $request->getSchemeAndHttpHost() . $url;
        }

        return $url;
    }

    public function fileSizeFilter($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);
        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1000, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;
            if ($currentValue < 1000) {
                break;
            }
        }

        return sprintf('%.1f', $currentValue) . $currentUnit;
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

        $text = str_replace(array("\n", "\r", "\t") , '', $text);
        $text = str_replace('&nbsp;' , ' ', $text);
        $text = trim($text);

        $length = (int) $length;
        if ( ($length > 0) && (mb_strlen($text) > $length) )  {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public function subTextFilter($text, $length = null)
    {
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t") , '', $text);
        $text = str_replace('&nbsp;' , ' ', $text);
        $text = trim($text);

        $length = (int) $length;
        if ( ($length > 0) && (mb_strlen($text,'utf-8') > $length) )  {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public function getFileType($fileName,$string=null)
    {
        $fileName=explode(".", $fileName);

        $name=strtolower($fileName[1]);
        if($string) $name=strtolower($fileName[1]).$string;

        return $name;
    }

    public function chrFilter($index)
    {
        return chr($index);
    }

    public function isHideThread($id)
    {
        $need=ServiceKernel::instance()->createService('Group.ThreadService')->sumGoodsCoinsByThreadId($id);

        $thread=ServiceKernel::instance()->createService('Group.ThreadService')->getThread($id);
        
        $data=explode('[/hide]',$thread['content']);
        foreach ($data as $key => $value) {

            $value=" ".$value;
            sscanf($value,"%[^[][hide=reply]%[^$$]",$replyContent,$replyHideContent);
            if($replyHideContent)
                return true;
        }

        if($need) return true;

        return false;
    }

    public function bbCode2HtmlFilter($bbCode)
    {
        $ext = $this;

        $bbCode = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function($matches) use ($ext) {
            $src = $ext->getFileUrl($matches[1]);
            return "<img src='{$src}' />";
        }, $bbCode);

        $bbCode = preg_replace_callback('/\[audio.*?id="(\d+)"\](.*?)\[\/audio\]/i', function($matches) {
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

    public function fillQuestionStemTextFilter($stem)
    {
        return preg_replace('/\[\[.+?\]\]/', '____', $stem);
    }

    public function fillQuestionStemHtmlFilter($stem)
    {
        $index = 0;
        $stem = preg_replace_callback('/\[\[.+?\]\]/', function($matches) use (&$index) {
            $index ++;
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


    public function getPurifyHtml($html,$trusted=false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => ServiceKernel::instance()->getParameter('kernel.cache_dir') .  '/htmlpurifier'
        );

        $factory = new HTMLPurifierFactory($config);
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
        $coinSettings = ServiceKernel::instance()->createService('System.SettingService')->get('coin',array());

        if($coinSettings['coin_enabled'] == 1 and $coinSettings['price_type'] == 'coin'){
                if ($order['amount'] == 0  and $order['coinAmount'] == 0 ){
                    $default = "无";
                }
                else{
                    $default = "余额支付";
                }
        }

        if ($coinSettings['coin_enabled'] != 1 or $coinSettings['price_type'] != 'coin') {
                if ($order['coinAmount'] > 0) {
                    $default = "余额支付";
                }
                else{
                    if ($order['amount'] == 0 ){
                        $default = "无";
                    }
                    else{
                        $default = "支付宝";
                    }
                }
            }

        return $default;
    }

    public function calculatePercent($number, $total)
    {
        if ($number == 0 or $total == 0) {
            return '0%';
        }

        if ($number >= $total) {
            return '100%';
        }
        return intval($number / $total * 100) . '%';
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
        return DataDict::dict($type);
    }

    public function getDictText($type, $key)
    {
        return DataDict::text($type, $key);
    }

    public function getUploadMaxFilesize($formated = true)
    {
        $max = FileToolkit::getMaxFilesize();
        if ($formated) {
            return FileToolkit::formatFileSize($max);
        }
        return $max;
    }

    public function getName ()
    {
        return 'topxia_web_twig';
    }

    public function getFreeLimitType($course){
        if(!empty($course['freeStartTime']) && !empty($course['freeEndTime'])) {
            $startTime = $course['freeStartTime'];
            $endTime = $course['freeEndTime'];
            $now = time();

            if($startTime > $now) {
                return 'free_coming';//即将限免
            } elseif ($endTime >= $now){
                return 'free_now';//正在限免
            } elseif ($endTime < $now){
                return 'free_end';//限免结束
            } else {
                return 'no_free';
            }
        } else {
            return 'no_free';
        }
    }

    public function blur_phone_number($phoneNum)
    {
        $head = substr($phoneNum,0,3);
        $tail = substr($phoneNum,-4,4);
        return ($head . '****' . $tail);
    }

    public function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) 
    { 
        $both_ends = $ltrim && $rtrim; 

        $char_class_inner = preg_replace( 
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ), 
            array( '\\\\\\0', '\\' ), 
            $charlist 
        ); 

        $work_horse = '[' . $char_class_inner . ']+'; 
        $ltrim && $left_pattern = '^' . $work_horse; 
        $rtrim && $right_pattern = $work_horse . '$'; 

        if($both_ends) 
        { 
            $pattern_middle = $left_pattern . '|' . $right_pattern; 
        } 
        elseif($ltrim) 
        { 
            $pattern_middle = $left_pattern; 
        } 
        else 
        { 
            $pattern_middle = $right_pattern; 
        } 

        return preg_replace("/$pattern_middle/usSD", '', $string); 
    } 

}


