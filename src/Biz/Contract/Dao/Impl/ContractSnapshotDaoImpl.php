<?php

namespace Biz\Contract\Dao\Impl;

use Biz\Contract\Dao\ContractSnapshotDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContractSnapshotDaoImpl extends GeneralDaoImpl implements ContractSnapshotDao
{
    protected $table = 'contract_snapshot';

    public function getByVersion($version)
    {
        return $this->getByFields(['version' => $version]);
    }

    public function declares()
    {
        return [
            'conditions' => [
                'id in (:ids)',
            ],
            'timestamps' => [
                'createdTime',
            ],
        ];
    }
}
