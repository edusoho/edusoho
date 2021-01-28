<?php

namespace  Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DownloadFileRecordDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
