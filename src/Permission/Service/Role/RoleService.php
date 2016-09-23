<?php
namespace Permission\Service\Role;

interface RoleService
{
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
