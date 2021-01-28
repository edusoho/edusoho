<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\DownloadFileRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DownloadFileRecordDaoImpl extends GeneralDaoImpl implements DownloadFileRecordDao
{
    protected $table = 'download_file_record';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
