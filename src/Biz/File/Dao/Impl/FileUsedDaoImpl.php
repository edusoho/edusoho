<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\FileUsedDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FileUsedDaoImpl extends GeneralDaoImpl implements FileUsedDao
{
    protected $table = 'file_used';

    public function declares()
    {
        return array(
            'conditions' => array(
                'id = :id',
                'type = :type',
                'targetType = :targetType',
                'targetId = :targetId',
                'targetId IN ( :targetIds )',
                'fileId = :fileId',
                'fileId IN (:fileIds)',
            ),
            'orderbys' => array(
                'createdTime',
            ),
        );
    }
}
