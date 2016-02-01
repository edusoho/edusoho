<?php
namespace Mooc\WebBundle\Twig\Extension;

use Symfony\Component\Yaml\Yaml;

class WebExtension extends \Twig_Extension
{
    protected $container;

    protected $pageScripts;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'mooc_web_twig';
    }

    public function getFilters()
    {
        return array(
            'num2chinese' => new \Twig_Filter_Method($this, 'num2chinese')
        );
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new \Twig_SimpleFunction('currentTime', array($this, 'getCurrentTime'), $options),
            new \Twig_SimpleFunction('in_menu_blacklist', array($this, 'inMenuBlacklist')),
            new \Twig_SimpleFunction('js_paths', array($this, 'getJsPaths')),
        );
    }

    public function getJsPaths()
    {

        $paths = call_user_func(array(new \Topxia\WebBundle\Twig\Extension\WebExtension($this->container), 'getJsPaths'));

        $basePath = $this->container->get('request')->getBasePath();
        $names[]  = 'moocweb';
        $names[]  = 'moocadmin';

        foreach ($names as $name) {
            $name                   = strtolower($name);
            $paths["{$name}bundle"] = "{$basePath}/bundles/{$name}/js";
        }

        return $paths;
    }

    public function getCurrentTime()
    {
        return time();
    }

    public function num2chinese($num)
    {
        $char    = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $dw      = array("", "十", "百", "千", "万", "亿", "兆");
        $retval  = "";
        $proZero = false;

        for ($i = 0; $i < strlen($num); $i++) {
            if ($i > 0) {
                $temp = (int) (($num % pow(10, $i + 1)) / pow(10, $i));
            } else {
                $temp = (int) ($num % pow(10, 1));
            }

            if (true == $proZero && 0 == $temp) {
                continue;
            }

            if (0 == $temp) {
                $proZero = true;
            } else {
                $proZero = false;
            }

            if ($proZero) {
                if ("" == $retval) {
                    continue;
                }

                $retval = $char[$temp].$retval;
            } else {
                $retval = $char[$temp].$dw[$i].$retval;
            }
        }

        if (strpos($retval, "一十") === 0) {
            $retval = preg_replace('/^一十/', '十', $retval);
        }

        return $retval;
    }

    public function inMenuBlacklist($code = '')
    {
        if (empty($code)) {
            return false;
        }

        $filename = $this->container->getParameter('kernel.root_dir').'/../app/config/menu_blacklist.yml';

        if (!file_exists($filename)) {
            return false;
        }

        $yaml      = new Yaml();
        $blackList = $yaml->parse(file_get_contents($filename));

        if (empty($blackList)) {
            $blackList = array();
        }

        return in_array($code, $blackList);
    }
}
