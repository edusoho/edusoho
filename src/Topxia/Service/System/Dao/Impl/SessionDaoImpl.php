<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\SessionDao;

class SessionDaoImpl extends BaseDao implements SessionDao
{
	protected $table = "session";

	public function getOnlineCount($retentionTime)
	{
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE `session_time` BETWEEN (unix_timestamp(now())-{$retentionTime}) AND (unix_timestamp(now()));";
		return $this->getConnection()->fetchColumn($sql) ? : null;
	}
}