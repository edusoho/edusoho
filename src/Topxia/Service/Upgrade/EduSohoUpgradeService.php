<?php
namespace Topxia\Service\Upgrade;

interface EduSohoUpgradeService 
{
	public function check($packages);
	public function upgrade($packId);
	public function install($packId);
	public function downloadPackage($uri,$filename);
	public function getPackage($packId);
}