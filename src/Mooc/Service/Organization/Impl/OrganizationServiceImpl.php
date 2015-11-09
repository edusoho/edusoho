<?php
namespace Mooc\Service\Organization\Impl;


use Mooc\Service\Organization\OrganizationService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;

class OrganizationServiceImpl extends BaseService implements OrganizationService
{

    public function getOrganization($id)
    {
        if ($id <= 0) {
            return array();
        }
        return $this->getOrganizationDao()->getOrganization($id);
    }

    public function findOrganizationsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }

        $conditions = array('organizationIds' => $ids);
        $orderBy = array('createdTime', 'DESC');
        $count = $this->getOrganizationDao()->searchOrganizationCount($conditions);
        return $this->getOrganizationDao()->searchOrganizations($conditions, $orderBy, 0, $count);
    }

    public function findOrganizationsByParentId($parentId)
    {
        $conditions = array('parentId' => $parentId);
        $orderBy = array('createdTime', 'DESC');
        $count = $this->getOrganizationDao()->searchOrganizationCount($conditions);
        return $this->getOrganizationDao()->searchOrganizations($conditions, $orderBy, 0, $count);
    }

    public function deleteOrganization($id)
    {
        $organization = $this->getOrganization($id);
        if(empty($organization)){
            $this->createNotFoundException();
        }
        $this->dispatchEvent('organization.delete', $organization['id']);
        $this->getOrganizationDao()->deleteOrganization($organization['id']);
        $this->getLogService()->info('Organization', 'delete', "删除院系/组织,{$organization['name']}(#{$organization['id']})");
    }

    public function addOrganization($organization)
    {
        $organization = $this->getOrganizationDao()->addOrganization($organization);
        $this->getLogService()->info('Organization', 'create', "添加院系/组织,{$organization['name']}(#{$organization['id']})");
        return $organization;
    }


    public function searchOrganizations($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrganizationDao()->searchOrganizations($conditions, $orderBy, $start, $limit);
    }

    public function getOrganizationTree()
    {
        $organizations = $this->findAllOrganizations();

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

    public function updateOrganization($id, $fields)
    {
        return $this->getOrganizationDao()->updateOrganization($id, $fields);
    }

    public function findAllOrganizations()
    {
        return ArrayToolkit::index($this->getOrganizationDao()->findAllOrganizations(),'id');
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

    public function findOrganizationChildrenIds($id)
    {
        $organization = $this->getOrganization($id);
        if (empty($organization)) {
            return array();
        }
        $tree = $this->getOrganizationTree();

        $childrenIds = array();
        $depth = 0;
        foreach ($tree as $node) {
            if ($node['id'] == $organization['id']) {
                $depth = $node['depth'];
                continue;
            }
            if ($depth > 0 && $depth < $node['depth']) {
                $childrenIds[] = $node['id'];
            }

            if ($depth > 0 && $depth >= $node['depth']) {
                break;
            }
        }

        return $childrenIds;
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getOrganizationDao()
    {
        return $this->createDao('Mooc:Organization.OrganizationDao');
    }

}