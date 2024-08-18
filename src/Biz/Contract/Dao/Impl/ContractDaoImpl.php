<?php

namespace Biz\Contract\Dao\Impl;

use Biz\Contract\Dao\ContractDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContractDaoImpl extends GeneralDaoImpl implements ContractDao
{
    protected $table = 'contract';

    public function declares()
    {
        return [
            'conditions' => [
                'name like :nameLike',
                'updatedUserId in (:updatedUserIds)',
            ],
            'serializes' => [
                'sign' => 'json',
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
        ];
    }
}
