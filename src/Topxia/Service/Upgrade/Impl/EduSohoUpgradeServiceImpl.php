<?php
namespace Topxia\Service\Upgrade\Impl;

use Topxia\Service\Upgrade\EduSohoUpgradeService;
use Topxia\Service\Common\BaseService;

class EduSohoUpgradeServiceImpl extends BaseService implements EduSohoUpgradeService 
{
	CONST BASE_URL = 'http://www.edusoho.com/';
	CONST CHECK_URL = 'upgrade/check';
	CONST COMMIT_URL = 'upgrade/commit';
	CONST GET_URL = 'upgrade/get';

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

	public function commit($id,$result)
	{
		$postData = array('id'=>$id);
		$postData['result'] = $result;
		$postData['client'] = $this->getClientInfo();
		$sendJsonData = json_encode($postData);
		$ch = curl_init(self::BASE_URL.self::COMMIT_URL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sendJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($sendJsonData))
		);
		curl_exec($ch);
		curl_close($ch);
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

	public function downloadPackage($uri,$filename)
	{
    	$path = $this->getKernel()->getParameter('topxia.disk.upgrade_dir').
    			DIRECTORY_SEPARATOR.$filename;    	
    	$this->download(str_replace(" ","%20",self::BASE_URL.$uri),$path);
    	return 	$path;
	}

	private function download($file_source, $file_target) 
	{
		$ch = curl_init($file_source);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$data = curl_exec($ch);
    	curl_close($ch);
    	file_put_contents($file_target, $data);
	}



	private function getClientInfo()
	{
		return array(
			'ip'=> gethostbyname($_SERVER['SERVER_NAME']),
			'host'=>$_SERVER['SERVER_NAME']
			);
	}
}
