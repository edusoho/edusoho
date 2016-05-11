<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 5/9/16
 * Time: 19:42
 */
namespace Topxia\Service\Org\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Org\OrgService;
use Topxia\Service\Common\BaseService;

class OrgServiceImpl extends BaseService implements OrgService
{
    public function createOrg($org)
    {
        $user = $this->getCurrentUser();

        $org = ArrayToolkit::parts($org, array('name', 'code', 'parentId', 'seq', 'description'));

        if (!ArrayToolkit::requireds($org, array('name', 'code', 'seq'))) {
            throw $this->createServiceException('缺少必要字段,添加失败');
        }

        $org['createdUserId'] = $user['id'];
        $org['createdTime']   = time();

        $org = $this->getOrgDao()->createOrg($org);

        if (isset($org['parentId']) && $org['parentId'] > 0) {
            $parentOrg = $this->getOrgDao()->getOrg($org['parentId']);
            $this->getOrgDao()->wave($parentOrg['id'], array('childrenNum' => +1));
            $org['orgCode'] = $parentOrg['orgCode'].$org['id'].'.';
            $org['depth']   = $parentOrg['depth'] + 1;
        } else {
            $org['orgCode'] = $org['id'].'.';
            $org['depth']   = 1;
        }

        //更新当前深度以及内部编码
        $org = $this->getOrgDao()->updateOrg($org['id'], array('orgCode' => $org['orgCode'], 'depth' => $org['depth']));
        return $org;
    }

    public function updateOrg($id, $fields)
    {
        $org = $this->checkBeforProccess($id);

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'parentId', 'seq', 'description'));

        if (!ArrayToolkit::requireds($fields, array('name', 'code', 'seq'))) {
            throw $this->createServiceException('缺少必要字段,添加失败');
        }

        $fields['updateTime'] = time();

        $org = $this->getOrgDao()->updateOrg($id, $fields); //更新当前深度以及内部编码
        return $org;
    }

    public function deleteOrg($id)
    {
        $org = $this->checkBeforProccess($id);

        if ($org['parentId']) {
            $this->getOrgDao()->wave($org['parentId'], array('childrenNum' => -1));
            $this->getOrgDao()->delete($id);
        }

        //删除辖下
        $this->getOrgDao()->deleteOrgsByOrgCode($org['orgCode']);
    }

    public function switchOrg($id)
    {
        $org  = $this->checkBeforProccess($id);
        $user = $this->getCurrentUser();

        $data                  = $user->toArray();
        $data['selectOrgCode'] = $org['orgCode'];
        $user->fromArray($data);
        $this->getKernel()->setCurrentUser($user);
    }

    public function getOrg($id)
    {
        return $this->getOrgDao()->getOrg($id);
    }

    public function findOrgsByOrgCode($orgCode = null)
    {
        //是否需要对该api做用户权限处理

        return $this->getOrgDao()->findOrgsByOrgCode($orgCode);
    }

    public function isCodeAvaliable($value, $exclude)
    {
        $org = $this->getOrgDao()->getOrgByCode($value);

        if (empty($org)) {
            return true;
        } else {
            return ($org['code'] === $exclude) ? true : false;
        }
    }

    private function checkBeforProccess($id)
    {
        $org = $this->getOrg($id);

        if (empty($org)) {
            throw $this->createServiceException('组织机构不存在,更新失败');
        }

        return $org;
    }

    protected function getOrgDao()
    {
        return $this->createDao('Org.OrgDao');
    }
}
