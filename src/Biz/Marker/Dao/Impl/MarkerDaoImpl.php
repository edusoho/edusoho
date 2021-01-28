<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\MarkerDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MarkerDaoImpl extends GeneralDaoImpl implements MarkerDao
{
    protected $table = 'marker';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByMediaId($mediaId)
    {
        return $this->findByFields(array('mediaId' => $mediaId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'mediaId = :mediaId',
                'second = :second',
            ),
        );
    }
}
