<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\MenuBuilder;

class PermissionExtension extends \Twig_Extension
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
            new \Twig_SimpleFilter('parent_permission', array($this, 'getParentPermission'))
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('permission', array($this, 'getPermissionByCode')),
            new \Twig_SimpleFunction('sub_permissions', array($this, 'getSubPermissions')),
            new \Twig_SimpleFunction('permission_crumb', array($this, 'getPermissionCrumb'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_permission', array($this, 'renderPermission'))
        );
    }

    public function renderPermission($parentCode, $template)
    {
        $children = $this->getSubPermissions($parentCode);
        return $this->container->get('templating')->render($template, array('permissions' => $children));
    }

    public function getPermissionCrumb($env, $context, $menu)
    {
        $menus = $this->getSubPermissions('admin', $menu['code'], '1');

        if ($menus) {
            $menu  = current($menus);
            $menus = $this->getSubPermissions('admin', $menu['code'], '1');

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

    public function getPermissionByCode($position, $code)
    {
        return $this->createMenuBuilder($position)->getMenuByCode($code);
    }

    public function getSubPermissions($position, $code, $group = null)
    {
        return $this->createMenuBuilder($position)->getMenuChildren($code, $group);
    }

    public function getParentPermission($code, $position = 'admin')
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
