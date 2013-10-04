<?php
namespace Topxia\Service\Upgrade;

interface UpgradeService 
{

	CONST PackTypeForInstall = 0;

	CONST PackTypeForUpgrade = 1;

	function check($packages,$clientInfo);

	function upgrade($package);

	function install($package);


	function addPackage($package);

	function deletePackage($id);

	function updatePackage($id,$package);
}