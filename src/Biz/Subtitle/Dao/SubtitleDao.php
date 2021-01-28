<?php

namespace Biz\Subtitle\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SubtitleDao extends GeneralDaoInterface
{
    public function findSubtitlesByMediaId($mediaId);
}
