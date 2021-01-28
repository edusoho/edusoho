<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileInitDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileInitDaoImpl extends GeneralDaoImpl implements UploadFileInitDao
{
    protected $table = 'upload_file_inits';

    public function getByGlobalId($globalId)
    {
        return $this->getByFields(array(
            'globalId' => $globalId,
        ));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'metas2' => 'json',
                'metas' => 'json',
                'convertParams' => 'json',
            ),
        );
    }
}
