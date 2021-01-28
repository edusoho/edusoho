<?php

namespace Biz\Org\Service;

interface OrgService
{
    public function createOrg($org);

    public function updateOrg($id, $fields);

    public function getOrg($id);

    public function findOrgsByIds($ids);

    public function deleteOrg($id);

    public function getOrgByOrgCode($orgCode);

    public function getOrgByCode($code);

    /**
     *  获取后台管理组织机构数据
     *  如果没有传orgcode, 默认获取所有.
     */
    public function findOrgsByPrefixOrgCode($orgCode = null);

    public function isCodeAvaliable($value, $exclude);

    /**
     * 切换组织机构,将用户选择的组织机构写入当前用户中.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    public function switchOrg($id);

    public function sortOrg($ids);

    public function searchOrgs($conditions, $orderBy, $start, $limit);

    /**
     * @param  $id
     *
     * @return orgName1->orgName2->orgName3
     */
    public function geFullOrgNameById($id);

    /**
     * @param [type] $module  , 要更新的模块名
     * @param [type] $ids,    要更新Ids
     * @param [type] $orgCode
     */
    public function batchUpdateOrg($module, $ids, $orgCode);

    /**
     * @param $name orgName
     * @param $parentId  orgParentId
     *
     * @return mixed is name avaliable under the parent
     */
    public function isNameAvaliable($name, $parentId, $exclude);

    /**
     * 给定组织机构id,获取该组织机构(含辖下)的所有模块的数据.
     *
     * @param $orgId
     *
     * @return mixed
     */
    public function findRelatedModuleCounts($orgId);
}
