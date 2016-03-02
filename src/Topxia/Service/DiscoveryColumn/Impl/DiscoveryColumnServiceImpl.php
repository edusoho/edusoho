<?php

namespace Topxia\Service\DiscoveryColumn\Impl;

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
        $fields = ArrayToolkit::parts($fields, array('categoryId', 'orderType', 'type', 'showCount', 'title'));
        return $this->getDiscoveryColumnDao()->updateDiscoveryColumn($id, $showFields);
    }

    public function deleteDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->deleteDiscoveryColumn($id);
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
