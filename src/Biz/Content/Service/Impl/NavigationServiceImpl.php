<?php

namespace Biz\Content\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Content\Dao\NavigationDao;
use Biz\Content\Service\NavigationService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use AppBundle\Common\ArrayToolkit;

class NavigationServiceImpl extends BaseService implements NavigationService
{
    public function getNavigation($id)
    {
        return $this->getNavigationDao()->get($id);
    }

    public function findNavigations($start, $limit)
    {
        return $this->getNavigationDao()->find($start, $limit);
    }

    public function getNavigationsCount()
    {
        return $this->getNavigationDao()->countAll();
    }

    public function getNavigationsCountByType($type)
    {
        return $this->getNavigationDao()->countByType($type);
    }

    public function findNavigationsByType($type, $start, $limit)
    {
        return $this->getNavigationDao()->findByType($type, $start, $limit);
    }

    public function searchNavigationCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getNavigationDao()->count($conditions);
    }

    public function searchNavigations($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getNavigationDao()->search($conditions, $orderBy, $start, $limit);
    }

    // TODO: org
    private function _prepareSearchConditions($conditions)
    {
        $magic = $this->getSettingService()->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            $user = $this->getCurrentUser();
            $conditions['orgId'] = $user->getSelectOrgId();
        }

        return $conditions;
    }

    public function getOpenedNavigationsTreeByType($type)
    {
        $user = $this->getCurrentUser();
        $conditions = array(
            'type' => $type,
            'isOpen' => 1,
            'orgId' => $user->getSelectOrgId(),
        );

        $count = $this->searchNavigationCount($conditions);
        if ($count == 0) {
            return array();
        }

        $navigations = $this->searchNavigations(
            $conditions,
            array('sequence' => 'ASC'),
            0,
            $count
        );

        $navigations = ArrayToolkit::index($navigations, 'id');

        foreach ($navigations as $index => $nav) {
            //只显示Open菜单
            // if (empty($nav['isOpen']) || $nav['isOpen'] != 1) {
            //     unset($navigations[$index]);
            //     continue;
            // }

            //一级菜单 - 保留
            if ($nav['parentId'] == 0) {
                continue;
            }

            //二级菜单

            //如果父菜单不存在(被删除)，子菜单不显示
            if (!isset($navigations[$nav['parentId']])) {
                unset($navigations[$index]);
                continue;
            }

            //如果父菜单是close的，子菜单不显示
            $parent = $navigations[$nav['parentId']];

            if ((empty($parent['isOpen']) || $parent['isOpen'] != 1)) {
                unset($navigations[$index]);
                continue;
            }

            //初始化父菜单的children数组
            if (empty($navigations[$nav['parentId']]['children'])) {
                $navigations[$nav['parentId']]['children'] = array();
            }

            //子菜单是open的，放到父菜单中
            if ($nav['isOpen']) {
                $navigations[$nav['parentId']]['children'][] = $nav;
                unset($navigations[$index]);
            }
        }

        return $navigations;
    }

    public function getNavigationsListByType($type)
    {
        $conditions = array('type' => $type);
        $count = $this->searchNavigationCount($conditions);
        $navigations = $this->searchNavigations(
            $conditions,
            array('sequence' => 'ASC'),
            0,
            $count
        );

        $prepare = function ($navigations) {
            $prepared = array();
            foreach ($navigations as $nav) {
                if (!isset($prepared[$nav['parentId']])) {
                    $prepared[$nav['parentId']] = array();
                }

                $prepared[$nav['parentId']][] = $nav;
            }

            return $prepared;
        };

        $navigations = $prepare($navigations);

        $tree = array();
        $this->makeNavigationTreeList($tree, $navigations, 0);

        return $tree;
    }

    protected function makeNavigationTreeList(&$tree, &$navigations, $parentId)
    {
        static $depth = 0;
        static $leaf = false;
        if (isset($navigations[$parentId]) && is_array($navigations[$parentId])) {
            foreach ($navigations[$parentId] as $nav) {
                ++$depth;
                $nav['depth'] = $depth;
                $tree[] = $nav;
                $this->makeNavigationTreeList($tree, $navigations, $nav['id']);
                --$depth;
            }
        }

        return $tree;
    }

    public function createNavigation($fields)
    {
        $keysArray = array('name', 'url', 'isOpen', 'isNewWin', 'type', 'sequence', 'parentId');
        $keysOfFields = array_keys($fields);
        foreach ($keysOfFields as $key => $keyOfFields) {
            if (!in_array($keyOfFields, $keysArray)) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }
        }

        $fields = $this->setNavigationOrg($fields);
        $fields['createdTime'] = $fields['updateTime'] = time();
        $fields['sequence'] = $this->getNavigationDao()->countByType($fields['type']) + 1;
        $result = $this->getNavigationDao()->create($fields);

        $this->getLogService()->info('info', 'navigation_create', "创建导航{$fields['name']}");

        $this->dispatchEvent('navigation.operate', $this->getNavigation($result['id']));

        return $result;
    }

    protected function setNavigationOrg($fields)
    {
        $magic = $this->getSettingService()->get('magic');

        if (empty($magic['enable_org'])) {
            return $fields;
        }

        $user = $this->getCurrentUser();
        $currentOrg = $user['org'];

        if (empty($fields['parentId'])) {
            if (empty($user['org'])) {
                return $fields;
            }
            $fields['orgId'] = $currentOrg['id'];
            $fields['orgCode'] = $currentOrg['orgCode'];
        } else {
            $parentNavigation = $this->getNavigation($fields['parentId']);
            $fields['orgId'] = $parentNavigation['orgId'];
            $fields['orgCode'] = $parentNavigation['orgCode'];
        }

        return $fields;
    }

    public function updateNavigation($id, $fields)
    {
        if (isset($fields['id'])) {
            unset($fields['id']);
        }

        $fields['updateTime'] = time();

        $this->getLogService()->info('info', 'navigation_update', "编辑导航#{$id}", $fields);

        return $this->getNavigationDao()->update($id, $fields);
    }

    public function updateNavigationsSequenceByIds($ids)
    {
        $index = 1;
        foreach ($ids as $key => $id) {
            $this->updateNavigation($id, array('sequence' => $index));
            ++$index;
        }
    }

    public function deleteNavigation($id)
    {
        $this->dispatchEvent('navigation.operate', $this->getNavigation($id));

        return ($this->getNavigationDao()->delete($id)) + ($this->getNavigationDao()->deleteByParentId($id));
    }

    /**
     * @return NavigationDao
     */
    protected function getNavigationDao()
    {
        return $this->createDao('Content:NavigationDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
