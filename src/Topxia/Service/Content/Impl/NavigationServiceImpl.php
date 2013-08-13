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

    public function editNavigation($id, $fields)
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

    public function createNavigation($fields)
    {
        $keysArray = array('name', 'url', 'openNewWindow', 'status', 'type', 'sequence');
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

    public function getNavigationsCount()
    {
        return $this->getNavigationDao()->getNavigationsCount();
    }

    public function getTopNavigationsCount()
    {
        return $this->getNavigationDao()->getNavigationsCountByType('top');
    }

    public function getFootNavigationsCount()
    {
        return $this->getNavigationDao()->getNavigationsCountByType('foot');
    }

    public function findNavigations($start, $limit)
    {
        return $this->getNavigationDao()->findNavigations($start, $limit);
    }

    public function findTopNavigations($start, $limit)
    {
        return $this->getNavigationDao()->findNavigationsByType('top', $start, $limit);
    }

    public function findFootNavigations($start, $limit)
    {
        return $this->getNavigationDao()->findNavigationsByType('foot', $start, $limit);
    }

    private function getNavigationDao()
    {
        return $this->createDao('Content.NavigationDao');
    }
    
}
