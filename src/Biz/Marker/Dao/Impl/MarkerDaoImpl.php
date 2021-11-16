<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\MarkerDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MarkerDaoImpl extends AdvancedDaoImpl implements MarkerDao
{
    protected $table = 'marker';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByMediaIdAndSecond($mediaId, $second)
    {
        return $this->getByFields(['mediaId' => $mediaId, 'second' => $second]);
    }

    public function declares()
    {
        return [
            'serializes' => ['activityIds' => 'delimiter'],
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'mediaId = :mediaId',
                'activityIds like :activityIds',
                'second = :second',
            ],
        ];
    }
}
