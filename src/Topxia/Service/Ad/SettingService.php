<?php
namespace Topxia\Service\Ad;

interface SettingService
{

	public function getSetting($id);

	public function findSettingsByIds(array $ids);

	public function createSetting($setting);

	public function searchSettings($conditions,$sort,$start,$limit);

	public function searchSettingCount($conditions);
	

}