<?php
namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\EduSohoUpgradeService;
use Topxia\Service\Common\BaseService;

class EduSohoUpgradeServiceImpl implements EduSohoUpgradeService
{
	CONST CHECK_URL = 'http://www.edusoho-dev.com/upgrade/check';
	CONST UPGRADE_URL = 'http://www.edusoho-dev.com/upgrade/upgrade';
	CONST INSTALL_URL = 'http://www.edusoho-dev.com/upgrade/install';
	CONST BASE_URL = 'http://www.edusoho-dev.com/';

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
		$result = json_decode(curl_exec($ch));
		curl_close($ch);
		return $result;
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
		$result = json_decode(curl_exec($ch));
		curl_close($ch);
		return $result;
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
		$result = json_decode(curl_exec($ch));
		curl_close($ch);
		return $result;
	}

	public function download($uri,$filename){
		$ch = curl_init(self::BASE_URL.$uri);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$data = curl_exec($ch);
    	curl_close($ch);
    	$path = $this->getContainer()->getParameter('topxia.disk.upgrade_dir').DIRECTORY_SEPARATOR.$filename;
    	file_put_contents($path, $data);	
    	return 	$path;
	}

	private function getClientInfo(){
		return array(
			'ip'=>$_SERVER['SERVER_ADDR'],
			'host'=>$_SERVER['SERVER_NAME']
			);
	}
}
