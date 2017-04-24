<?php

namespace Biz\Util\Dao\Impl;

use Biz\Util\Dao\MediaParseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MediaParseDaoImpl extends GeneralDaoImpl implements MediaParseDao
{
    protected $table = 'media_parse';

    public function getMediaParseByUuid($uuid)
    {
        return $this->getByFields('uuid', $uuid);
    }

    public function getMediaParseByHash($hash)
    {
        return $this->getByFields('hash', $hash);
    }

    public function declares()
    {
        return array();
    }
}
