<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\MenuBuilder;
use Symfony\Component\Yaml\Yaml;

class MenuExtension extends \Twig_Extension
{
    protected $container;

    protected $menuUtil = null;

    protected $builders = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('menu_children', array($this, 'getMenuChildren')),
            new \Twig_SimpleFunction('menu_breadcrumb', array($this, 'getMenuBreadcrumb')),
            new \Twig_SimpleFunction('menu_path', array($this, 'getMenuPath'), array('needs_context' => true, 'needs_environment' => true))
        );
    }

    public function getMenuPath($env, $context, $menu)
    {
        $route  = empty($menu['router_name']) ? $menu['code'] : $menu['router_name'];
        $params = empty($menu['router_params']) ? array() : $menu['router_params'];

        if (!empty($menu['router_params_context'])) {
            foreach ($params as $key => $value) {
                $value        = explode('.', $value, 2);
                $params[$key] = $context['_context'][$value[0]][$value[1]];
            }
        }

        return $this->container->get('router')->generate($route, $params);
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

    public function getMenuChildren($position, $code, $group = null)
    {
        return $this->createMenuBuilder($position)->getMenuChildren($code, $group);
    }

    public function getMenuBreadcrumb($position, $code)
    {
        return $this->createMenuBuilder($position)->getMenuBreadcrumb($code);
    }

    private function createMenuBuilder($position)
    {
        if (!isset($this->builders[$position])) {
            $this->builders[$position] = new MenuBuilder($position);
        }
        return $this->builders[$position];
    }

    public function getName()
    {
        return 'topxia_menu_twig';
    }
}
