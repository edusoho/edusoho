<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\MenuBuilder;

class MenuExtension extends \Twig_Extension
{
    protected $container;

    protected $menuUtil = null;

    protected $builders = array();

    protected $levelOneMenus = array();

    protected $levelTwoMenus = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parent_menu', array($this, 'getParentMenu'))
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_menu_by_code', array($this, 'getMenuByCode')),
            new \Twig_SimpleFunction('menu_children', array($this, 'getMenuChildren')),
            new \Twig_SimpleFunction('menu_path', array($this, 'getMenuPath'), array('needs_context' => true, 'needs_environment' => true))
        );
    }

    public function getMenuPath($env, $context, $menu)
    {
        $menus = $this->getMenuChildren('admin', $menu['code'], '1');

        if ($menus) {
            $menu  = current($menus);
            $menus = $this->getMenuChildren('admin', $menu['code'], '1');

            if ($menus) {
                $menu = current($menus);
            }
        }

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

    public function getMenuByCode($position, $code)
    {
        return $this->createMenuBuilder($position)->getMenuByCode($code);
    }

    public function getMenuChildren($position, $code, $group = null)
    {
        return $this->createMenuBuilder($position)->getMenuChildren($code, $group);
    }

    public function getParentMenu($code, $position = 'admin')
    {
        return $this->createMenuBuilder($position)->getParentMenu($code);
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
