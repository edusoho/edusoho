<?php

namespace Topxia\Service\DiscoveryColumn\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\DiscoveryColumn\DiscoveryColumnService;

class DiscoveryColumnServiceImpl extends BaseService implements DiscoveryColumnService
{
	public function getDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->getDiscoveryColumn($id);
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        $DiscoveryColumn = $this->getDiscoveryColumn($id);
        $showFields = array(
            'id' => $id,
            'categoryId' => $fields['categoryId'],
            'orderType' => $fields['orderType'],
            'type' => $fields['type'],
            'showCount' => $fields['showCount'],
            'title' => $fields['title'],
            'updateTime' => time()
            ); 
        return $this->getDiscoveryColumnDao()->updateDiscoveryColumn($id, $showFields);
    }

    public function deleteDiscoveryColumn($id)
    {
        $this->getDiscoveryColumnDao()->deleteDiscoveryColumn($id);
        return true;
    }

    public function addDiscoveryColumn($fields)
    {
    	return $this->getDiscoveryColumnDao()->addDiscoveryColumn($fields);
    }

    public function findDiscoveryColumnByTitle($title)
    {
        return $this->getDiscoveryColumnDao()->findDiscoveryColumnByTitle($title);
    }

    public function getAllDiscoveryColumns()
    {
        return $this->getDiscoveryColumnDao()->getAllDiscoveryColumns();
    }

	protected function getDiscoveryColumnDao()
    {
        return $this->createDao('DiscoveryColumn.DiscoveryColumnDao');
    }
}