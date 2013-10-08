<?php
namespace Topxia\Service\Upgrade;

interface UpgradeService 
{
	public function check();

	public function upgrade($id);

	public function install($id);

	public function searchPackageCount();

	public function searchPackages($start, $limit);
}