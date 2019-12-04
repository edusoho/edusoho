<?php

namespace AppBundle\Twig;

use Biz\Role\Util\PermissionBuilder;
use Topxia\Service\Common\ServiceKernel;

class PermissionExtension extends \Twig_Extension
{
    protected $container;

    protected $builder = null;

    protected $loader = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parent_permission', array($this, 'getParentPermission')),
            new \Twig_SimpleFilter('visible_menus', array($this, 'getVisibleMenus')),
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
            new \Twig_SimpleFunction('eval_expression', array($this, 'evalExpression'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('first_child_permission', array($this, 'getFirstChild')),
            new \Twig_SimpleFunction('first_child_permission_by_code', array($this, 'getFirstChildByCode')),
            new \Twig_SimpleFunction('side_bar_permission', array($this, 'getSideBar')),
            new \Twig_SimpleFunction('root_permission', array($this, 'getRootPermission')),
            new \Twig_SimpleFunction('nav_permission', array($this, 'getNavPermission')),
        );
    }

    /**
     * @param $code
     *
     * @return array
     *               获取admin_v2的sideBar
     */
    public function getSideBar($code)
    {
        $permission = $this->getNavPermission($code);
        $group = $this->createPermissionBuilder()->groupedV2Permissions($permission['code']);

        $permissionMenus = $this->buildSidebarPermissionMenus($group);

        return $permissionMenus;
    }

    /**
     * @param $menu
     * @param bool $filterVisible 默认过滤visible != false 的第一个
     *
     * @return array|mixed
     */
    public function getFirstChild($menu, $filterVisible = true)
    {
        $menus = $this->getSubPermissions($menu['code']);

        if (empty($menus)) {
            $permissions = $this->createPermissionBuilder()->getOriginSubPermissions($menu['code']);
            if (empty($permissions)) {
                return array();
            } else {
                $menus = $permissions;
            }
        }

        if ($filterVisible) {
            return $this->getFirstVisibleMenu($menus);
        }

        return current($menus);
    }

    public function getFirstChildByCode($code, $filterVisible = true)
    {
        $menus = $this->getSubPermissions($code);

        if (empty($menus)) {
            $permissions = $this->createPermissionBuilder()->getOriginSubPermissions($code);
            if (empty($permissions)) {
                return array();
            } else {
                $menus = $permissions;
            }
        }

        if ($filterVisible) {
            return $this->getFirstVisibleMenu($menus);
        }

        return current($menus);
    }

    private function getFirstVisibleMenu($menus)
    {
        $twig = $this->container->get('twig');
        foreach ($menus as $menu) {
            if (!isset($menu['visible']) || isset($menu['visible']) && $this->evalExpression($twig, array(), $menu['visible'])) {
                return $menu;
            }
        }

        return array();
    }

    /**
     * @param $menu
     *
     * @return array|mixed
     *                     递归获取叶子节点的permissions
     */
    public function getLeafFirstChild($menu)
    {
        $childMenu = $this->getFirstChild($menu);
        if ($childMenu['children']) {
            $childMenu = $this->getLeafFirstChild($childMenu);
        }

        return $childMenu;
    }

    public function getPermissionPath($env, $context, $menu)
    {
        $route = empty($menu['router_name']) ? $menu['code'] : $menu['router_name'];
        $params = empty($menu['router_params']) ? array() : $menu['router_params'];

        foreach ($params as $key => $value) {
            if (0 === strpos($value, '(')) {
                $value = $this->evalExpression($env, $context['_context'], $value);
                $params[$key] = $value;
            } else {
                $params[$key] = "{$value}";
            }
        }

        return $this->container->get('router')->generate($route, $params);
    }

    public function evalExpression($twig, $context, $code)
    {
        $code = trim($code);
        if (0 === strpos($code, '(')) {
            $code = substr($code, 1, strlen($code) - 2);
        } else {
            $code = "'{$code}'";
        }

        $loader = new \Twig_Loader_Array(array(
            'expression.twig' => '{{'.$code.'}}',
        ));

        $loader = new \Twig_Loader_Chain(array($loader, $twig->getLoader()));

        $twig->setLoader($loader);

        return $twig->render('expression.twig', $context);
    }

    public function getPermissionByCode($code)
    {
        return $this->createPermissionBuilder()->getOriginPermissionByCode($code);
    }

    public function hasPermission($code)
    {
        $currentUser = ServiceKernel::instance()->getCurrentUser();

        return $currentUser->hasPermission($code);
    }

    public function getSubPermissions($code, $group = null)
    {
        $permission = $this->getPermissionByCode($code);
        if (isset($permission['disable']) && $permission['disable']) {
            return $this->createPermissionBuilder()->getOriginSubPermissions($code, $group);
        } else {
            return $this->createPermissionBuilder()->getSubPermissions($code, $group);
        }
    }

    public function groupedPermissions($code)
    {
        return $this->createPermissionBuilder()->groupedPermissions($code);
    }

    public function getParentPermission($code)
    {
        $permission = $this->createPermissionBuilder()->getOriginPermissionByCode($code);

        if (isset($permission['disable']) && $permission['disable']) {
            $parent = $this->createPermissionBuilder()->getOriginPermissionByCode($permission['parent']);
        } else {
            $parent = $this->createPermissionBuilder()->getParentPermissionByCode($code);
        }

        return $parent;
    }

    public function getVisibleMenus($menus)
    {
        $twig = $this->container->get('twig');
        foreach ($menus as $key => $menu) {
            if (isset($menu['visible']) && !$this->evalExpression($twig, array(), $menu['visible'])) {
                unset($menus[$key]);
            }
        }

        return $menus;
    }

    /**
     * @param $code
     * @param string $type admin|admin_v2
     *
     * @return array|mixed
     *                     通过递归方式获取到root节点的permission
     */
    public function getRootPermission($code, $type = 'admin_v2')
    {
        $permission = $this->getParentPermission($code);
        if ($permission['code'] != $type) {
            $permission = $this->getRootPermission($permission['code']);
        }

        return $permission;
    }

    /**
     * @param $code
     * @param string $type admin|admin_v2
     *
     * @return array|mixed
     *                     通过递归方式获取到nav栏的permission
     */
    public function getNavPermission($code, $type = 'admin_v2')
    {
        $permission = $this->getParentPermission($code);
        if ($permission['parent'] != $type) {
            $permission = $this->getNavPermission($permission['code']);
        }

        return $permission;
    }

    private function createPermissionBuilder()
    {
        return PermissionBuilder::instance();
    }

    private function buildSidebarPermissionMenus($allGroup, $grade = 0)
    {
        $permissions = array();

        foreach ($allGroup as $key => $group) {
            //菜单组是否为可见状态
            if (isset($group['visible']) && !$this->canVisibleMenus($group['visible'])) {
                unset($allGroup[$key]);
                continue;
            }
            //组下面没有菜单，则不显示该组
            if (!isset($group['children'])) {
                continue;
            }

            $group = $this->buildGroupPermissionMenus($group);

            //组下有菜单才显示，如果没有显示的菜单则组也不显示
            if ($group['grade'] == 0 && isset($group['nodes'])) {
                $permissions[] = $group;
            }
        }

        return $permissions;
    }

    private function buildGroupPermissionMenus($group, $grade = 0)
    {
        $groupInfo = array();
        if (isset($group['is_group'])) {
            $groupInfo['grade'] = $grade;
        }
        $groupInfo['id'] = "group_{$group['code']}";
        $groupInfo['name'] = ServiceKernel::instance()->trans($group['name'], array(), 'menu');
        $groupInfo['class'] = isset($group['class']) ? $group['class'] : '';
        $groupInfo['code'] = $group['code'];

        foreach ($group['children'] as $k => $child) {
            //菜单是否可见状态
            if (isset($child['visible']) && !$this->canVisibleMenus($child['visible'])) {
                unset($group['children'][$k]);
                continue;
            }
            // 获取菜单组下面的节点菜单数据
            $groupInfo['nodes'][] = $this->buildNodesPermissionMenus($child);
        }

        return $groupInfo;
    }

    private function buildNodesPermissionMenus($child)
    {
        $nodes = array();
        $nodes['id'] = "menu_{$child['code']}";
        $nodes['class'] = isset($child['class']) ? $child['class'] : '';
        $nodes['name'] = ServiceKernel::instance()->trans($child['name'], array(), 'menu');
        $nodes['link'] = $this->getPermissionPath(array(), array(), $this->getFirstChild($this->getPermissionByCode($child['code'])));
        $nodes['grade'] = 1;
        $nodes['code'] = $child['code'];

        return $nodes;
    }

    private function canVisibleMenus($visible)
    {
        $twigExpressionResult = $this->evalExpression($this->container->get('twig'), array(), $visible);

        if ($twigExpressionResult) {
            return true;
        }

        return false;
    }

    private function removeEmptyGroup($permissions)
    {
        $array = array();
        foreach ($permissions as $key => $permission) {
            if ($permission['grade'] == 0 && !isset($permission['nodes'])) {
                unset($permissions[$key]);
            }
        }

        return $array;
    }

    public function getName()
    {
        return 'permission.permission_extension';
    }
}
