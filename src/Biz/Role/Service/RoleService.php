<?php
namespace Biz\Role\Service;

interface RoleService
{

    const ADMIN_FORBIDDEN_PERMISSIONS = array(
        'admin_user_avatar',
        'admin_user_change_password',
        'admin_my_cloud',
        'admin_cloud_video_setting',
        'admin_edu_cloud_sms',
        'admin_edu_cloud_search_setting',
        'admin_setting_cloud_attachment',
        'admin_setting_cloud',
        'admin_system'
    );

    public function getRole($id);

    public function getRoleByCode($code);

    public function findRolesByCodes(array $codes);

    public function createRole($role);

    public function updateRole($id, array $fiedls);

    public function deleteRole($id);

    public function searchRoles($conditions, $sort, $start, $limit);

    public function searchRolesCount($conditions);

    public function refreshRoles();
}
