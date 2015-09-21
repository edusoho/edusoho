<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 13:40
 */

namespace Custom\Service\School\Dao;


interface SchoolDao
{

    public function addSchoolOrganization($organization);

    public function getSchoolOrganization($id);

    public function searchSchoolOrganization($conditions, $orderBy, $start, $limit);

    public function findAllSchoolOrganization();

    public function updateSchoolOrganization($id, $fields);

    public function deleteSchoolOrganization($id);

    public function searchSchoolOrganizationCount($conditions);
}