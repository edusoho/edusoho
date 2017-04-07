<?php

namespace Biz\Subtitle\Dao\Impl;

use Biz\Subtitle\Dao\SubtitleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SubtitleDaoImpl extends GeneralDaoImpl implements SubtitleDao
{
    protected $table = 'subtitle';

    public function findSubtitlesByMediaId($mediaId)
    {
        return $this->findInField('mediaId', array($mediaId));
    }

    public function declares()
    {
        return array(
        );
    }
}
