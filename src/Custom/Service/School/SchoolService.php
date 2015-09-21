<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 11:22
 */

namespace Custom\Service\School;


interface SchoolService
{
    public function getSchoolOrganization($id);
    public function addSchoolOrganization($organization);
    public function getSchoolOrganizationTree();
    public function findAllSchoolOrganization();
    public function searchSchoolOrganization($conditions, $orderBy, $start, $limit);
    public function updateSchoolOrganization($id, $fields);
    public function deleteSchoolOrganization($id);
    public function findSchoolOrganizationsByParentId($parentId);
}