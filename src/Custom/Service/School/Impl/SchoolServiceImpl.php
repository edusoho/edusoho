<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 11:24
 */

namespace Custom\Service\School\Impl;


use Custom\Service\School\SchoolService;
use Topxia\Service\Common\BaseService;

class SchoolServiceImpl extends BaseService implements SchoolService
{

    public function getSchoolOrganization($id)
    {
        if ($id <= 0) {
            return array();
        }
        return $this->getSchoolDao()->getSchoolOrganization($id);
    }

    public function findSchoolOrganizationsByParentId($parentId)
    {
        $conditions = array('parentId' => $parentId);
        $orderBy = array('createdTime', 'DESC');
        $count = $this->getSchoolDao()->searchSchoolOrganizationCount($conditions);
        return $this->getSchoolDao()->searchSchoolOrganization($conditions, $orderBy, 0, $count);
    }

    public function deleteSchoolOrganization($id)
    {
        $this->getSchoolDao()->deleteSchoolOrganization($id);
    }

    public function addSchoolOrganization($organization)
    {
        return $this->getSchoolDao()->addSchoolOrganization($organization);
    }


    public function searchSchoolOrganization($conditions, $orderBy, $start, $limit)
    {
        return $this->getSchoolDao()->searchSchoolOrganization($conditions, $orderBy, $start, $limit);
    }

    public function getSchoolOrganizationTree()
    {
        $organizations = $this->findAllSchoolOrganization();

        $prepared = array();
        foreach ($organizations as $organization) {
            if (!isset($prepared[$organization['parentId']])) {
                $prepared[$organization['parentId']] = array();
            }
            $prepared[$organization['parentId']][] = $organization;
        }


        $tree = array();
        $this->makeOrganizationTree($tree, $prepared, 0);

        return $tree;
    }

    public function updateSchoolOrganization($id, $fields)
    {
        return $this->getSchoolDao()->updateSchoolOrganization($id, $fields);
    }

    public function findAllSchoolOrganization()
    {
        return $this->getSchoolDao()->findAllSchoolOrganization();
    }

    protected function makeOrganizationTree(&$tree, &$organizations, $parentId)
    {
        static $depth = 0;

        if (isset($organizations[$parentId]) && is_array($organizations[$parentId])) {
            foreach ($organizations[$parentId] as $organization) {
                $depth++;
                $organization['depth'] = $depth;
                $tree[] = $organization;
                $this->makeOrganizationTree($tree, $organizations, $organization['id']);
                $depth--;
            }
        }

        return $tree;
    }

    protected function getSchoolDao()
    {
        return $this->createDao('Custom:School.SchoolDao');
    }

}