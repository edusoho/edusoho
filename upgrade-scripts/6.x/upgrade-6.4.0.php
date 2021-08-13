<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateSetting();            
            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
     }

     private function updateSetting()
     {
        $auth = $this->getSettingService()->get('auth', array());
        //不再使用的setting参数
        if(isset($auth['registerSort']) && is_array($auth['registerSort'])){
            foreach ($auth['registerSort'] as $key => $value) {
                if ($value == 'nickname' || $value == 'password' ||  $value == 'confirmPassword'){
                    unset($auth['registerSort'][$key]);
                }
            }
        }
        //不再使用的setting参数
        if(isset($auth['registerFieldNameArray']) && is_array($auth['registerFieldNameArray'])){
            foreach ($auth['registerFieldNameArray'] as $key => $value) {
                if ($value == 'nickname' || $value == 'password' ||  $value == 'confirmPassword'){
                    unset($auth['registerFieldNameArray'][$key]);
                }
            }
        }
        
        $this->getSettingService()->set('auth', $auth);

        $cloudSms = $this->getSettingService()->get('cloud_sms', array());
        //短信验证使用场景:新用户注册时, 不再使用
        if(isset($cloudSms['sms_registration'])){
            $cloudSms['sms_registration']='off'; //TODO 下一个版本unset该变量 
        }
        //短信验证使用场景:忘记支付密码时, 不再使用
        if(isset($cloudSms['sms_forget_password'])){
            $cloudSms['sms_forget_password']='off'; //TODO 下一个版本unset该变量 
        }
        
        //短信验证使用场景:忘记登录密码时, 不再使用
        if(isset($cloudSms['sms_forget_pay_password'])){
            $cloudSms['sms_forget_pay_password']='off'; //TODO 下一个版本unset该变量 
        }        
        $this->getSettingService()->set('cloud_sms', $cloudSms);
     }
 }


 abstract class AbstractUpdater
 {
    protected $kernel;
    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    abstract public function update();
   
 }