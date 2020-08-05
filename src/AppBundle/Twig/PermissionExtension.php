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
        return [
            new \Twig_SimpleFilter('parent_permission', [$this, 'getParentPermission']),
            new \Twig_SimpleFilter('visible_menus', [$this, 'getVisibleMenus']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('permission', [$this, 'getPermissionByCode']),
            new \Twig_SimpleFunction('sub_permissions', [$this, 'getSubPermissions']),
            new \Twig_SimpleFunction('permission_path', [$this, 'getPermissionPath'], ['needs_context' => true, 'needs_environment' => true]),
            new \Twig_SimpleFunction('grouped_permissions', [$this, 'groupedPermissions']),
            new \Twig_SimpleFunction('has_permission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction('eval_expression', [$this, 'evalExpression'], ['needs_context' => true, 'needs_environment' => true]),
            new \Twig_SimpleFunction('first_child_permission', [$this, 'getFirstChild']),
            new \Twig_SimpleFunction('first_child_permission_by_code', [$this, 'getFirstChildByCode']),
            new \Twig_SimpleFunction('side_bar_permission', [$this, 'getSideBar']),
            new \Twig_SimpleFunction('root_permission', [$this, 'getRootPermission']),
            new \Twig_SimpleFunction('nav_permission', [$this, 'getNavPermission']),
        ];
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
        $groups = $this->createPermissionBuilder()->groupedV2Permissions($permission['code']);

        $permissionMenus = $this->buildSidebarPermissionMenus($groups);

        return $permissionMenus;
    }

    /**
     * @param $menu
     * @param bool $filterVisible         默认过滤visible != false 的第一个
     * @param bool $allowOriginPermission 是否允许加载整个树，忽略权限 （默认true做兼容）
     *
     * @return array|mixed
     */
    public function getFirstChild($menu, $filterVisible = true, $allowOriginPermission = true)
    {
        if (!$menu) {
            return [];
        }

        return $this->getFirstChildByCode($menu['code'], $filterVisible, $allowOriginPermission);
    }

    /**
     * @param $code
     * @param bool $filterVisible         默认过滤visible != false 的第一个
     * @param bool $allowOriginPermission 是否允许加载整个树，忽略权限 （默认true做兼容）
     *
     * @return array|mixed
     */
    public function getFirstChildByCode($code, $filterVisible = true, $allowOriginPermission = true)
    {
        $menus = $this->getSubPermissions($code);

        if (empty($menus)) {
            if (!$allowOriginPermission) {
                return [];
            }

            $permissions = $this->createPermissionBuilder()->getOriginSubPermissions($code);
            if (empty($permissions)) {
                return [];
            } else {
                $menus = $permissions;
            }
        }

        if ($filterVisible) {
            return $this->getFirstVisibleMenu($menus);
        }

        return current($menus);
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
        $params = empty($menu['router_params']) ? [] : $menu['router_params'];

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

        $loader = new \Twig_Loader_Array([
            'expression.twig' => '{{'.$code.'}}',
        ]);

        $loader = new \Twig_Loader_Chain([$loader, $twig->getLoader()]);

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
            if (isset($menu['visible']) && !$this->evalExpression($twig, [], $menu['visible'])) {
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
        $permissions = [];

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
            if (0 == $group['grade'] && isset($group['nodes'])) {
                $permissions[] = $group;
            }
        }

        return $permissions;
    }

    private function buildGroupPermissionMenus($group, $grade = 0)
    {
        $groupInfo = [];
        if (isset($group['is_group'])) {
            $groupInfo['grade'] = $grade;
        }
        $groupInfo['id'] = "group_{$group['code']}";
        $groupInfo['name'] = ServiceKernel::instance()->trans($group['name'], [], 'menu');
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
        $nodes = [];
        $nodes['id'] = "menu_{$child['code']}";
        $nodes['class'] = isset($child['class']) ? $child['class'] : '';
        $nodes['name'] = ServiceKernel::instance()->trans($child['name'], [], 'menu');
        $nodes['link'] = $this->getPermissionPath([], [], $this->getFirstChild($this->getPermissionByCode($child['code'])));
        $nodes['grade'] = 1;
        $nodes['code'] = $child['code'];

        return $nodes;
    }

    private function canVisibleMenus($visible)
    {
        $twigExpressionResult = $this->evalExpression($this->container->get('twig'), [], $visible);

        if ($twigExpressionResult) {
            return true;
        }

        return false;
    }

    private function getFirstVisibleMenu($menus)
    {
        $twig = $this->container->get('twig');
        foreach ($menus as $menu) {
            if (!isset($menu['visible']) || isset($menu['visible']) && $this->evalExpression($twig, [], $menu['visible'])) {
                return $menu;
            }
        }

        return [];
    }

    public function getName()
    {
        return 'permission.permission_extension';
    }
}
