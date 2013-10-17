<?php
namespace Topxia\Service\Upgrade;

interface UpgradeService 
{
	public function check();

	public function upgrade($id);

	public function install($id);

	public function checkEnvironment();
	public function checkDepends($id);
	public function downloadAndExtract($id);
	public function backUpSystem($id);
	public function beginUpgrade($id);
	public function refreshCache();
	public function hasLastError($id);
	// public function recovery();


	
	public function getRemoteInstallPackageInfo($id);

	public function getRemoteUpgradePackageInfo($id);

	public function searchPackageCount($conditions);

	public function searchPackages($conditions, $start, $limit);

	public function searchLogCount($conditions);

	public function searchLogs($conditions, $start, $limit);

}