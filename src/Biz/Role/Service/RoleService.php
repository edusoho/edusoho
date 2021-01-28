<?php

namespace Biz\Role\Service;

use Biz\System\Annotation\Log;

interface RoleService
{
    public function getRole($id);

    public function getRoleByCode($code);

    public function findRolesByCodes(array $codes);

    /**
     * @param $role
     *
     * @return mixed
     * @Log(module="role",action="create")
     */
    public function createRole($role);

    /**
     * @param $id
     * @param array $fiedls
     *
     * @return mixed
     * @Log(module="role",action="update",param="id")
     */
    public function updateRole($id, array $fiedls);

    /**
     * @param $id
     *
     * @return mixed
     * @Log(module="role",action="delete")
     */
    public function deleteRole($id);

    public function searchRoles($conditions, $sort, $start, $limit);

    public function searchRolesCount($conditions);

    public function refreshRoles();

    public function rolesTreeTrans($tree, $type = 'admin');

    public function splitRolesTreeNode($tree, &$permissions = array());

    public function getAllParentPermissions($permissions);

    public function getParentRoleCodeArray($code, $nodes, &$parentCodes = array());

    public function filterRoleTree($tree);

    public function getPermissionsYmlContent();
}
