<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityLearnLogDao extends GeneralDaoInterface
{
	public function sumLearnedTimeByActivityIdAndUserId($activityId, $userId);

	public function findActivityLearnLogsByActivityIdAndUserIdAndEvent($activityId, $userId, $event);
}
