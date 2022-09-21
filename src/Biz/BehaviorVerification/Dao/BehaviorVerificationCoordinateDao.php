<?php

namespace Biz\BehaviorVerification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BehaviorVerificationCoordinateDao extends GeneralDaoInterface
{
    public function getByCoordinate($coordinate);

    public function getTop10();
}
