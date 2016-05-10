<?php
namespace Permission\Service\Role;

interface RoleService
{
    public function getRole($id);

    public function getRoleByCode($Code);

    public function createRole($role);

    public function updateRole($id, array $fiedls);

    public function searchRoles($conditions, $sort, $start, $limit);

    public function searchRolesCount($conditions);
}
