<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\LocationDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class LocationDaoImpl extends AdvancedDaoImpl implements LocationDao
{
    protected $table = 'infomation_collect_location';

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
            ],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
