<?php

namespace Org\Service\Org\Impl;

use Org\Service\Org\OrgService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Org\Service\Org\Dao\Impl\OrgDaoImpl;
use Org\Service\Org\OrgBatchUpdateFactory;

class OrgServiceImpl extends BaseService implements OrgService
{
    public function createOrg($org)
    {
        $user = $this->getCurrentUser();

        $org = ArrayToolkit::parts($org, array('name', 'code', 'parentId', 'description'));

        if (!ArrayToolkit::requireds($org, array('name', 'code'))) {
            throw $this->createServiceException($this->getServiceKernel()->trans('缺少必要字段,添加失败'));
        }

        $org['createdUserId'] = $user['id'];

        $org = $this->getOrgDao()->createOrg($org);

        $parentOrg = $this->updateParentOrg($org);

        $org = $this->updateOrgCodeAndDepth($org, $parentOrg);

        return $org;
    }

    private function updateParentOrg($org)
    {
        $parentOrg = null;

        if (isset($org['parentId']) && $org['parentId'] > 0) {
            $parentOrg = $this->getOrgDao()->getOrg($org['parentId']);
            $this->getOrgDao()->wave($parentOrg['id'], array('childrenNum' => +1));
        }

        return $parentOrg;
    }

    private function updateOrgCodeAndDepth($org, $parentOrg)
    {
        $fields = array();

        if (empty($parentOrg)) {
            $fields['orgCode'] = $org['id'].'.';
            $fields['depth']   = 1;
        } else {
            $fields['orgCode'] = $parentOrg['orgCode'].$org['id'].'.';
            $fields['depth']   = $parentOrg['depth'] + 1;
        }

        return $this->getOrgDao()->updateOrg($org['id'], $fields);
    }

    public function updateOrg($id, $fields)
    {
        $org = $this->checkBeforProccess($id);

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'parentId', 'description'));

        if (!ArrayToolkit::requireds($fields, array('name', 'code'))) {
            throw $this->createServiceException($this->getServiceKernel()->trans('缺少必要字段,添加失败'));
        }

        $org = $this->getOrgDao()->updateOrg($id, $fields);
        return $org;
    }

    public function deleteOrg($id)
    {
        $org  = $this->checkBeforProccess($id);
        $that = $this;

        $this->getOrgDao()->getConnection()->transactional(function () use ($org, $id, $that) {
            if ($org['parentId']) {
                $that->getOrgDao()->wave($org['parentId'], array('childrenNum' => -1));
            }
            $that->getOrgDao()->delete($id);
            //删除辖下
            $that->getOrgDao()->deleteOrgsByPrefixOrgCode($org['orgCode']);
        });
    }

    public function switchOrg($id)
    {
        $user = $this->getCurrentUser();

        $data              = $user->toArray();
        $data['selectOrg'] = $this->checkBeforProccess($id);
        $user->fromArray($data);
        $this->getKernel()->setCurrentUser($user);
    }

    public function getOrgByOrgCode($orgCode)
    {
        return $this->getOrgDao()->getOrgByOrgCode($orgCode);
    }

    public function getOrg($id)
    {
        return $this->getOrgDao()->getOrg($id);
    }

    public function findOrgsByIds($ids)
    {
        return $this->getOrgDao()->findOrgsByIds($ids);
    }

    public function findOrgsByPrefixOrgCode($orgCode = null)
    {
        //是否需要对该api做用户权限处理
        if (empty($orgCode)) {
            $user    = $this->getCurrentUser();
            $org     = $this->getOrg($user['orgId']);
            $orgCode = $org['orgCode'];
        }

        return $this->getOrgDao()->findOrgsByPrefixOrgCode($orgCode);
    }

    public function isCodeAvaliable($value, $exclude)
    {
        $org = $this->getOrgDao()->getOrgByCode($value);

        if (empty($org)) {
            return true;
        }
        return ($org['code'] === $exclude);
    }

    private function checkBeforProccess($id)
    {
        $org = $this->getOrg($id);

        if (empty($org)) {
            throw $this->createServiceException($this->getServiceKernel()->trans('组织机构不存在,更新失败'));
        }

        return $org;
    }

    public function sortOrg($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getOrgDao()->updateOrg($id, array('seq' => $index));
        }
    }

    public function searchOrgs($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrgDao()->searchOrgs($conditions, $orderBy, $start, $limit);
    }

    public function getOrgByCode($code)
    {
        return $this->getOrgDao()->getOrgByCode($code);
    }

    public function geFullOrgNameById($id, $orgs = array())
    {
        $orgs[] = $org = $this->getOrg($id);
        if (isset($org['parentId'])) {
            return $this->geFullOrgNameById($org['parentId'], $orgs);
        } else {
            $orgs = ArrayToolkit::index($orgs, 'id');
            ksort($orgs);
            $orgs = ArrayToolkit::column($orgs, 'name');
            return implode($orgs, '->');
        }
    }

    public function batchUpdateOrg($module, $ids, $orgCode)
    {
        $this->getModuleService($module)->batchUpdateOrg(explode(',', $ids), $orgCode);
    }

    public function isNameAvaliable($name, $parentId, $exclude)
    {
        $org = $this->getOrgDao()->findOrgByNameAndParentId($name, $parentId);
        if (empty($org)) {
            return true;
        }
        return ($org['id'] == $exclude);
    }

    public function findRelatedModuleDatas($orgId)
    {
        $org          = $this->getOrg($orgId);
        $modules      = OrgBatchUpdateFactory::getModules();
        $modalesDatas = array();
        $conditions   = array('likeOrgCode' => $org['orgCode']);
        foreach ($modules as $module => $service) {
            $dispay                = OrgBatchUpdateFactory::getDispayModuleName($module);
            $modalesDatas[$dispay] = $this->createService($service)->searchCount($conditions);
        }
        return array_filter($modalesDatas);
    }

    /**
     * @return OrgDaoImpl
     */
    public function getOrgDao()
    {
        return $this->createDao('Org:Org.OrgDao');
    }

    protected function getModuleService($module)
    {
        $moduleService = OrgBatchUpdateFactory::getModuleService($module);
        return $this->createService($moduleService);
    }
}
