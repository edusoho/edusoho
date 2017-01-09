<?php

namespace Biz\Util\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MediaParseDao extends GeneralDaoInterface
{
    public function getMediaParseByUuid($uuid);

    public function getMediaParseByHash($hash);
}
