<?php

namespace Topxia\Service\Ad\Dao;

interface SettingDao
{
	public function getSetting($id);

	public function findSettingsByIds(array $ids);

    public function searchSettings($conditions, $orderBy, $start, $limit);

    public function searchSettingCount($conditions);

    public function addSetting($guest);

	public function updateSetting($id, $fields);


}