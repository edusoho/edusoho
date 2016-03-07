<?php

namespace Topxia\Service\DiscoveryColumn\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\DiscoveryColumn\DiscoveryColumnService;
use Topxia\Common\ArrayToolkit;

class DiscoveryColumnServiceImpl extends BaseService implements DiscoveryColumnService
{
    public function getDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->getDiscoveryColumn($id);
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        
        $fields = ArrayToolkit::parts($fields, array('categoryId', 'orderType', 'type', 'showCount', 'title', 'seq'));
        return $this->getDiscoveryColumnDao()->updateDiscoveryColumn($id, $fields);
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

    public function sortDiscoveryColumns(array $ids)
    {
        $index = 1;
        foreach ($ids as $key => $id) {
            $this->updateDiscoveryColumn($id, array('seq' => $index));
            $index++;
        }
    }

    protected function getDiscoveryColumnDao()
    {
        return $this->createDao('DiscoveryColumn.DiscoveryColumnDao');
    }
}
