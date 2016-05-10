<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Common\MenuBuilder;
use Eexit\Twig\ContextParser\ContextParser;

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
            new \Twig_SimpleFunction('grouped_permissions', array($this, 'groupedPermissions')),
            new \Twig_SimpleFunction('has_permission', array($this, 'hasPermission')),
            new \Twig_SimpleFunction('eval_expression', array($this, 'evalExpression'), array('needs_context' => true, 'needs_environment' => true))
        );
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

    public function evalExpression($twig, $context, $code)
    {
        $loader = new \Twig_Loader_Array(array(
            'expression.twig' => '{{'.$code.'}}',
        ));

        $twig = new \Twig_Environment($loader);

        return $twig->render('expression.twig', $context);
    }

    public function getPermissionByCode($code)
    {
        return $this->createMenuBuilder()->getMenuByCode($code);
    }

    public function hasPermission($code)
    {
        $permission = $this->createMenuBuilder()->getMenuByCode($code);
        return !empty($permission);
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

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName()
    {
        return 'topxia_permission_twig';
    }
}
