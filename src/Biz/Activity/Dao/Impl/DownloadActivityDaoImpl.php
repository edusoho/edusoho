<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\DownloadActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DownloadActivityDaoImpl extends GeneralDaoImpl implements DownloadActivityDao
{
    protected $table = 'activity_download';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['fileIds' => 'json'],
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
