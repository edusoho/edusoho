<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 5/9/16
 * Time: 19:42
 */
namespace Topxia\Service\Org;

interface OrgService
{
    public function createOrg($org);

    public function updateOrg($id, $fields);

    public function getOrg($id);

    public function deleteOrg($id);
    /**
     *  获取后台管理组织机构的treeTable数据
     */
    public function findOrgTablelist();

    public function isCodeAvaliable($value, $exclude);
}
