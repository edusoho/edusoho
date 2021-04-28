<?php

namespace Biz\WeChat\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SubscribeRecordDaoImpl extends AdvancedDaoImpl implements SubscribeRecordDao
{
    protected $table = 'wechat_subscribe_record';

    public function declares()
    {
        return [
            'orderbys' => [],
            'conditions' => [
                'id = :id',
            ],
            'timestamps' => ['createdTime', 'updatedTime'],
        ];
    }
}
