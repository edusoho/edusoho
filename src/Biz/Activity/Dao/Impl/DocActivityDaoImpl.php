<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\DocActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DocActivityDaoImpl extends GeneralDaoImpl implements DocActivityDao
{
    protected $table = 'activity_doc';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                /*S2B2C 增加syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
        ];
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
