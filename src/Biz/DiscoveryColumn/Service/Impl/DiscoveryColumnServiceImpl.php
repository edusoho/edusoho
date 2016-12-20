<?php

namespace Biz\DiscoveryColumn\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;

class DiscoveryColumnServiceImpl extends BaseService implements DiscoveryColumnService
{
    public function getDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->get($id);
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('categoryId', 'orderType', 'type', 'showCount', 'title', 'seq'));
        return $this->getDiscoveryColumnDao()->update($id, $fields);
    }

    public function deleteDiscoveryColumn($id)
    {
        return $this->getDiscoveryColumnDao()->delete($id);
    }

    public function addDiscoveryColumn($fields)
    {
        return $this->getDiscoveryColumnDao()->create($fields);
    }

    public function findDiscoveryColumnByTitle($title)
    {
        return $this->getDiscoveryColumnDao()->findByTitle($title);
    }

    public function getAllDiscoveryColumns()
    {
        return $this->getDiscoveryColumnDao()->findAllOrderBySeq();
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
        return $this->createDao('DiscoveryColumn:DiscoveryColumnDao');
    }
}
