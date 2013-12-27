<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;

class WebExtension extends \Twig_Extension
{
    protected $container;

    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getFilters ()
    {
        return array(
            'smart_time' => new \Twig_Filter_Method($this, 'smarttimeFilter') ,
            'time_range' => new \Twig_Filter_Method($this, 'timeRangeFilter'),
            'remain_time' => new \Twig_Filter_Method($this, 'remainTimeFilter'),
            'location_text' => new \Twig_Filter_Method($this, 'locationTextFilter'),
            'tags_html' => new \Twig_Filter_Method($this, 'tagsHtmlFilter', array('is_safe' => array('html'))),
            'file_size'  => new \Twig_Filter_Method($this, 'fileSizeFilter'),
            'plain_text' => new \Twig_Filter_Method($this, 'plainTextFilter', array('is_safe' => array('html'))),
            'duration'  => new \Twig_Filter_Method($this, 'durationFilter'),
            'tags_join' => new \Twig_Filter_Method($this, 'tagsJoinFilter'),
            'navigation_url' => new \Twig_Filter_Method($this, 'navigationUrlFilter'),
            'chr' => new \Twig_Filter_Method($this, 'chrFilter')
        );
    }

    public function getFunctions()
    {
        return array(
            'file_uri_parse'  => new \Twig_Function_Method($this, 'parseFileUri'),
            'file_path'  => new \Twig_Function_Method($this, 'getFilePath'),
            'object_load'  => new \Twig_Function_Method($this, 'loadObject'),
            'setting' => new \Twig_Function_Method($this, 'getSetting') ,
            'percent' => new \Twig_Function_Method($this, 'calculatePercent') ,
            'category_choices' => new \Twig_Function_Method($this, 'getCategoryChoices') ,
            'dict' => new \Twig_Function_Method($this, 'getDict') ,
            'dict_text' => new \Twig_Function_Method($this, 'getDictText', array('is_safe' => array('html'))) ,
            'upload_max_filesize' => new \Twig_Function_Method($this, 'getUploadMaxFilesize') ,
        );
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
            return $url;
        } else {

        }
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
        
        $length = (int) $length;
        if ( ($length > 0) && (mb_strlen($text) > $length) )  {
            $text = mb_substr($text, 0, $length, 'UTF-8');
            $text .= '...';
        }

        return $text;
    }

    public function chrFilter($index)
    {
        return chr($index);
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


