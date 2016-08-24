<?php
namespace Permission\Service\Role\Dao;

interface RoleDao
{
    public function getRole($id);

    public function getRoleByCode($Code);

    public function findRolesByCodes($codes);

    public function createRole($role);

    public function updateRole($id, array $fiedls);

    public function deleteRole($id);

    public function searchRoles($conditions, $orderBy, $start, $limit);

    public function searchRolesCount($conditions);
}
