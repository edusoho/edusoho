<?php
namespace Topxia\Service\Upgrade;

interface EduSohoUpgradeService 
{
	function check($packages);
	function upgrade($packId);
	function install($packId);
}