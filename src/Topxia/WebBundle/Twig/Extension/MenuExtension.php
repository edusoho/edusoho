<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\MenuBuilder;
use Topxia\WebBundle\Util\Permission;

class MenuExtension extends \Twig_Extension
{
    protected $container;

    protected $menuUtil = null;

    protected $builders = array();


    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            'menus' => new \Twig_Function_Method($this, 'menus'),
            'html_title' => new \Twig_Function_Method($this, 'htmlTitle'),
            'page_title' => new \Twig_Function_Method($this, 'pageTitle'),
            'menu_children' => new \Twig_Function_Method($this, 'getMenuChildren'),
            'menu_breadcrumb' => new \Twig_Function_Method($this, 'getMenuBreadcrumb'),
            'menu_path' => new \Twig_Function_Method($this, 'getMenuPath', array('needs_context' => true, 'needs_environment' => true)),

        );
    }

    public function getMenuPath($env, $context, $menu)
    {
        $route = empty($menu['router_name']) ? $menu['code'] : $menu['router_name'];
        $params = empty($menu['router_params']) ? array() : $menu['router_params'];

        if (!empty($menu['router_params_context'])) {
            foreach ($params as $key => $value) {
                $value = explode('.', $value, 2);
                $params[$key] = $context['_context'][$value[0]][$value[1]];

            }
        }

        return $this->container->get('router')->generate($route, $params);
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

    public function htmlTitle($position, $code)
    {
        if ($position != 'admin') {
            throw new \RuntimeException('menu position error.');
        }
        return $this->createMenuUtil()->getTitle($code);
    }

    public function pageTitle($position, $code)
    {
        if ($position != 'admin') {
            throw new \RuntimeException('menu position error.');
        }
        return $this->createMenuUtil()->getTitle2($code);
    }

    public function menus($position, $parent = null, $group = null)
    {
        if ($position != 'admin') {
            throw new \RuntimeException('menu position error.');
        }
        return $this->createMenuUtil()->getPermissions($parent, $group);
    }

    private function createMenuUtil()
    {
        if (!$this->menuUtil) {
            $this->menuUtil = new Permission();
        }

        return $this->menuUtil;
    }

    public function getName ()
    {
        return 'topxia_menu_twig';
    }

}


