<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 13:40
 */

namespace Custom\Service\Organization\Dao;


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