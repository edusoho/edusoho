<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\DownloadActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DownloadActivityDaoImpl extends GeneralDaoImpl implements DownloadActivityDao
{
    protected $table = 'activity_download';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('fileIds' => 'json'),
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
