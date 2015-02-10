<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class EduCloudController extends BaseController
{
	public function smsSendAction(Request $request)
	{
		if ($request->getMethod() ==  'POST'){
			$currentTime = time();

			$smsLastTime = $request->getSession()->get('sms_last_time');
			$this->checkLastTime($smsLastTime);

			$smsType = $request->getSession()->get('sms_type');
			$this->checkSmsType($smsType);

			$smsCode = $this->generateSmsCode();
			$to = $request->request->get('to');
			$this->checkPhoneNum($to); 
			try{
			  $result = $this->getEduCloudService()->sendSms($to, $smsCode);
			  if (isset($result['error'])){
			    return $this->createJsonResponse(array('error' => 'failed to send sms'));
			  }
			}catch(\RuntimeException $e){
			  return $this->createJsonResponse(array('error' => 'failed to send sms'));
			}

			$request->getSession()->set('sms_code', $smsCode);
			$request->getSession()->set('sms_last_time', $currentTime);
			$request->getSession()->set('sms_type', $smsType);

			return $this->createJsonResponse(array('ACK' => 'ok'));
		}
		return $this->createJsonResponse(array('error' => 'GET method'));		
	}

	private function generateSmsCode($length = 6)
	{
		$code = rand(0,9);
		for ($i = 1; $i < $length; $i++){
			$code = $code .rand(0,9);
		}
		return $code;
	}

	private function checkPhoneNum($num)
	{
		if (!preg_match("/^1\d{10}$/", $num)){
			throw new \RuntimeException('phone num error');	
		}
	}

	private function checkLastTime($smsLastTime)
	{
		if (!((strlen($smsLastTime)==0)||(($currentTime-$smsLastTime)>1800))){
			throw new \RuntimeException('wait 30 minutes');				
		}
	}

	private function checkSmsType($smsType)
	{
		$user = $this->getCurrentUser();
        if((!$user->isLogin())&&($smsType=='sms_user_pay'||$smsType=='sms_find_pay_password')) {
            throw new \RuntimeException('用户未登陆');	
        }
        if(($user->isLogin())&&($smsType=='sms_registration')) {
            throw new \RuntimeException('用户已经登陆');	
        }
	}

	protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');   
    }
}