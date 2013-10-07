<?php
namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\EduSohoUpgradeService;
use Topxia\Service\Common\BaseService;

class EduSohoUpgradeServiceImpl implements EduSohoUpgradeService
{
	CONST CHECK_URL = 'http://www.edusoho-dev.com/upgrade/check';
	CONST UPGRADE_URL = 'http://www.edusoho-dev.com/upgrade/upgrade';
	CONST INSTALL_URL = 'http://www.edusoho-dev.com/upgrade/install';

	public function check($packages){
		$postData = array('packages'=>$packages);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::CHECK_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		var_dump(curl_exec($ch));
	}
	public function upgrade($packId){
		$postData = array('id'=>$packId);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::UPGRADE_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		var_dump(curl_exec($ch));
	}
	public function install($packId){
		$postData = array('id'=>$packId);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::INSTALL_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		var_dump(curl_exec($ch));
	}

	private function getClientInfo(){
		return array(
			'ip'=>$_SERVER['SERVER_ADDR'],
			'host'=>$_SERVER['SERVER_NAME']
			);
	}
}
