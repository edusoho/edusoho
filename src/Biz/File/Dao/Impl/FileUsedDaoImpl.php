<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\FileUsedDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class FileUsedDaoImpl extends AdvancedDaoImpl implements FileUsedDao
{
    protected $table = 'file_used';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'id = :id',
                'type = :type',
                'type IN ( :types )',
                'targetType = :targetType',
                'targetType IN (:targetTypes)',
                'targetId = :targetId',
                'targetId IN ( :targetIds )',
                'fileId = :fileId',
                'fileId IN (:fileIds)',
            ),
            'orderbys' => array(
                'createdTime',
                'id',
            ),
        );
    }
}
