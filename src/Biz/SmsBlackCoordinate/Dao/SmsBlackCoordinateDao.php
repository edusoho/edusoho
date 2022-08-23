<?php

namespace Biz\SmsBlackCoordinate\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SmsBlackCoordinateDao extends GeneralDaoInterface
{
    public function getByCoordinate($coordinate);

    public function getTop10();
}
