<?php
namespace Topxia\Service\Upgrade;

interface EduSohoUpgradeService 
{
	public function check($packages);
	public function commit($id,$result);
	public function downloadPackage($uri,$filename);
	public function getPackage($packId);
}