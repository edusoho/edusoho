<?php
namespace Mooc\Service\Organization\Dao;

interface OrganizationDao
{
    public function addOrganization($organization);

    public function getOrganization($id);

    public function searchOrganizations($conditions, $orderBy, $start, $limit);

    public function findAllOrganizations();

    public function updateOrganization($id, $fields);

    public function deleteOrganization($id);

    public function searchOrganizationCount($conditions);
}
