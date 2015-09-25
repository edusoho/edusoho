<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 11:22
 */

namespace Custom\Service\Organization;


interface OrganizationService
{
    public function getOrganization($id);
    public function addOrganization($organization);
    public function getOrganizationTree();
    public function findAllOrganizations();
    public function searchOrganizations($conditions, $orderBy, $start, $limit);
    public function updateOrganization($id, $fields);
    public function deleteOrganization($id);
    public function findOrganizationsByParentId($parentId);
}