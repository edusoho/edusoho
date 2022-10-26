<?php

namespace MarketingMallBundle\Biz\Role\Service\Impl;

use AppBundle\Common\Tree;
use Biz\Role\Service\Impl\RoleServiceImpl as BaseService;
use Biz\Role\Util\PermissionBuilder;
use MarketingMallBundle\Biz\Role\Service\RoleService;

class RoleServiceImpl extends BaseService implements RoleService
{
    public function refreshMarketingMallAdminRole()
    {
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        if (empty($mallSettings)) {
            return;
        }

        $permissions = PermissionBuilder::instance()->loadPermissionsFromAllConfig();
        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');
        $rolePermissions = $tree->find(function ($tree) {
            return 'admin_v2_marketing_mall' === $tree->data['code'];
        })->column('code');
        $rolePermissions = array_merge(['admin_v2'], array_values($rolePermissions));

        $marketingMallAdminRoleCode = 'ROLE_MARKETING_MALL_ADMIN';
        $marketingMallAdminRole = $this->getRoleDao()->getByCode($marketingMallAdminRoleCode);
        if (empty($marketingMallAdminRole)) {
            $marketingMallAdminRole = [
                'code' => $marketingMallAdminRoleCode,
                'name' => '商城管理员',
                'data' => [],
                'data_v2' => $rolePermissions,
                'createdUserId' => $this->getCurrentUser()->getId(),
            ];
            $this->getLogService()->info('role', 'init_create_role', '初始化商城管理员', $marketingMallAdminRole);
            $this->getRoleDao()->create($marketingMallAdminRole);
        } else {
            $this->getRoleDao()->update($marketingMallAdminRole['id'], ['data_v2' => $rolePermissions]);
        }
    }
}
