<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\NavigationService;
use Topxia\Common\ArrayToolkit;

class NavigationServiceImpl extends BaseService implements NavigationService
{
    public function getNavigation($id)
    {
        return $this->getNavigationDao()->getNavigation($id);
    }

    public function findNavigations($start, $limit)
    {
        return $this->getNavigationDao()->findNavigations($start, $limit);
    }

    public function getNavigationsCount()
    {
        return $this->getNavigationDao()->getNavigationsCount();
    }

    public function getNavigationsCountByType($type)
    {
        return $this->getNavigationDao()->getNavigationsCountByType($type);
    }

    public function findNavigationsByType($type, $start, $limit)
    {
        return $this->getNavigationDao()->findNavigationsByType($type, $start, $limit);
    }

    public function createNavigation($fields)
    {
        $keysArray = array('name', 'url', 'isOpen', 'isNewWin', 'type', 'sequence');
        $keysOfFields = array_keys($fields);
        foreach ($keysOfFields as $key => $keyOfFields) {
            if(!in_array($keyOfFields, $keysArray)){
                throw $this->createServiceException('添加的字段有问题！');
            }
        }
        $fields['createdTime'] = $fields['updateTime'] = time();
        $result = $this->getNavigationDao()->addNavigation($fields);
        return $result;
    }

    public function updateNavigation($id, $fields)
    {
        if(isset($fields['id'])){
            unset($fields['id']);
        }
        $fields['updateTime'] = time();
        return $this->getNavigationDao()->updateNavigation($id, $fields);
    }

    public function deleteNavigation($id)
    {
        return $this->getNavigationDao()->deleteNavigation($id);
    }

    private function getNavigationDao()
    {
        return $this->createDao('Content.NavigationDao');
    }
    
}
