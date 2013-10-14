<?php
namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\EduSohoUpgradeService;
use Topxia\Service\Common\BaseService;

class EduSohoUpgradeServiceImpl extends BaseService implements EduSohoUpgradeService 
{
	CONST BASE_URL = 'http://www.edusoho-dev.com/';

	CONST CHECK_URL = 'upgrade/check';
	CONST UPGRADE_URL = 'upgrade/upgrade';
	CONST GET_URL = 'upgrade/get';
	CONST INSTALL_URL = 'upgrade/install';

	public function check($packages)
	{
		$postData = array('packages'=>$packages);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::BASE_URL.self::CHECK_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		return $result;
	}

	public function upgrade($packId)
	{
		$postData = array('id'=>$packId);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::BASE_URL.self::UPGRADE_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		return $result;
	}

	public function getPackage($packId){
		$postData = $packId;
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::BASE_URL.self::GET_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		return $result;		
	}

	public function install($packId)
	{
		$postData = array('id'=>$packId);
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::BASE_URL.self::INSTALL_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		$result = json_decode(curl_exec($ch),true);
		curl_close($ch);
		return $result;
	}

	public function downloadPackage($uri,$filename)
	{
    	$path = $this->getKernel()->getParameter('topxia.disk.upgrade_dir').
    			DIRECTORY_SEPARATOR.$filename;    	
    	$this->download(str_replace(" ","%20",self::BASE_URL.$uri),$path);
    	return 	$path;
	}

	private function download($file_source, $file_target) 
	{
	    $rh = fopen($file_source, 'rb');
	    $wh = fopen($file_target, 'w+b');
	    if (!$rh || !$wh) {
	        	return false;
	    }
	    while (!feof($rh)) {
	        if (fwrite($wh, fread($rh, 4096)) === FALSE) {
	            return false;
	        }
	        flush();
	    }

	    fclose($rh);
	    fclose($wh);
    	return true;
	}



	private function getClientInfo()
	{
		return array(
			'ip'=>$_SERVER['SERVER_ADDR'],
			'host'=>$_SERVER['SERVER_NAME']
			);
	}
}
