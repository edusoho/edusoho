<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityLearnLogDao extends GeneralDaoInterface
{
	public function sumLearnTimeByActivityIdAndUserId($activityId, $userId);
}
