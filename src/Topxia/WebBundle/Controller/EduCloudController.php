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
			$currentUser = $this->getCurrentUser();
			$currentTime = time();

			$smsLastTime = $request->getSession()->get('sms_last_time');
			$this->checkLastTime($smsLastTime);

			$smsType = $request->getSession()->get('sms_type');
			$this->checkSmsType($smsType, $currentUser);
			$user = $currentUser->toArray();

			$smsCode = $this->generateSmsCode();
			if ( isset($user['verifiedMobile']) && (strlen($user['verifiedMobile'])>0) && ($smsType !='sms_registration') ){
				$to = $user['verifiedMobile'];
			}else{
				$to = $request->request->get('to');				
			}
			$this->checkPhoneNum($to); 
			
			//必须 开启短信验证、适用场景、并且用户已经有verifiedMobile
			if (($smsType == 'sms_forget_password')&&($to != $user['verifiedMobile'])){
				return $this->createJsonResponse(array('error' => 'mobile not match'));
			}

			try{
				$result = $this->getEduCloudService()->sendSms($to, $smsCode);
				if (isset($result['error'])){
				  return $this->createJsonResponse(array('error' => 'failed to send sms'));
			 	}
			}catch(\RuntimeException $e){
				return $this->createJsonResponse(array('error' => 'failed to send sms'));
			}

			$this->getLogService()->info('sms', 'sms', "对{$to}发送用于{$smsType}的验证短信{$smsCode}");

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

	private function checkSmsType($smsType, $user)
	{
		if (!in_array($smsType, array('sms_user_pay' , 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password'))){
			throw new \RuntimeException('不存在的sms Type');
		}

        if((!$user->isLogin())&&($smsType=='sms_user_pay'||$smsType=='sms_forget_pay_password')) {
            throw new \RuntimeException('用户未登陆');	
        }
	}

	protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');   
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}