<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\MenuBuilder;

class PermissionExtension extends \Twig_Extension
{
    protected $container;

    protected $builder = null;

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
            new \Twig_SimpleFunction('permission_path', array($this, 'getPermissionPath'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('render_permission', array($this, 'renderPermission')),
            new \Twig_SimpleFunction('grouped_permissions', array($this, 'groupedPermissions'))
        );
    }

    public function renderPermission($parentCode, $activePermission, $template)
    {
        $children = $this->getSubPermissions($parentCode);
        $template = "PermissionBundle:Templates:{$template}.html.twig";
        return $this->container->get('templating')->render($template, array(
            'permissions' => $children,
            'activePermission' => $activePermission,
        ));
    }

    public function getPermissionPath($env, $context, $menu)
    {
        $menus = $this->getSubPermissions($menu['code']);

        if ($menus) {
            $menu  = current($menus);
            $menus = $this->getSubPermissions($menu['code']);

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

    public function getPermissionByCode($code)
    {
        return $this->createMenuBuilder()->getMenuByCode($code);
    }

    public function getSubPermissions($code, $group = '1')
    {
        return $this->createMenuBuilder()->getMenuChildren($code, $group);
    }

    public function groupedPermissions($code)
    {
        return $this->createMenuBuilder()->groupedMenus($code);
    }

    public function getParentPermission($code)
    {
        return $this->createMenuBuilder()->getParentMenu($code);
    }

    private function createMenuBuilder()
    {
        if (empty($this->builder)) {
            $this->builder = new MenuBuilder();
        }

        return $this->builder;
    }

    public function getName()
    {
        return 'topxia_permission_twig';
    }
}
