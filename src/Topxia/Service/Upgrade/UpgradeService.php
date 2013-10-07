<?php
namespace Topxia\Service\Upgrade;

interface UpgradeService 
{
	function check();

	function upgrade($id);

	function install($id);
}