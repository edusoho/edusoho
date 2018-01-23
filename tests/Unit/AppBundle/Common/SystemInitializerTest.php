<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\SystemInitializer;
use AppBundle\Common\ReflectionUtils;
use Symfony\Component\Console\Output\ConsoleOutput;

class SystemInitializerTest extends BaseTestCase
{
    public function testInitStorageSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initStorageSetting', array());
        $result = $this->getSettingService()->get('storage'); 
        $this->assertArrayEquals( array(
            'upload_mode' => 'local',
            'cloud_api_server' => 'http://api.edusoho.net',
            'cloud_access_key' => '',
            'cloud_secret_key' => '',
        ), $result); 
    }

    public function testInitDefaultSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initDefaultSetting', array());
        $result = $this->getSettingService()->get('default'); 
        $this->assertArrayEquals(array(
            'chapter_name' => "章",
            'user_name' => "学员",
            'part_name' => "节",
        ), $result);  

        $result = $this->getSettingService()->get('post_num_rules'); 
        $this->assertArrayEquals(array(
            'rules' => array(
                'thread' => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum' => 100,
                    ),
                ),
                'threadLoginedUser' => array(
                    'fiveMuniteRule' => array(
                        'interval' => 300,
                        'postNum' => 50,
                    ),
                ),
            ),
        ), $result);

        $result = $this->getSettingService()->get('developer'); 
        $this->assertArrayEquals(array(), $result);  

        $result = $this->getSettingService()->get('developer'); 
        $this->assertArrayEquals(array(
            'cloud_api_failover' => 1
        ), $result);  
    }

    public function testInitPaymentSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initPaymentSetting', array());
        $result = $this->getSettingService()->get('payment'); 
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_accessKey' => '',
            'alipay_secretKey' => '',
        ), $result);  
    }

    public function testInitSiteSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initSiteSetting', array());
        $result = $this->getSettingService()->get('site'); 
        $this->assertArrayEquals(array(
            'name' => 'EDUSOHO测试站',
            'slogan' => '强大的在线教育解决方案',
            'url' => 'http://demo.edusoho.com',
            'logo' => '',
            'seo_keywords' => 'edusoho, 在线教育软件, 在线在线教育解决方案',
            'seo_description' => 'edusoho是强大的在线教育开源软件',
            'master_email' => 'test@edusoho.com',
            'icp' => ' 浙ICP备13006852号-1',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
        ), $result);    
    }

    public function testInitRefundSetting()
    {
         $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initRefundSetting', array());
        $result = $this->getSettingService()->get('refund'); 
        $this->assertArrayEquals(array(
            'maxRefundDays' => 10,
            'applyNotification' => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification' => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。',
        ), $result);    
    }
    public function testInitConsultSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initConsultSetting', array());
        $result = $this->getSettingService()->get('contact'); 
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'worktime' => '9:00 - 17:00',
            'qq' => array(
                array('name' => '', 'number' => ''),
            ),
            'qqgroup' => array(
                array('name' => '', 'number' => ''),
            ),
            'phone' => array(
                array('name' => '', 'number' => ''),
            ),
            'webchatURI' => '',
            'email' => '',
            'color' => 'default',
        ), $result);    
    }

    public function testInitMagicSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initMagicSetting', array());
        $result = $this->getSettingService()->get('magic'); 
        $this->assertArrayEquals(array(
            'export_allow_count' => 100000,
            'export_limit' => 10000,
            'enable_org' => 0,
        ), $result);    
    }

    public function testInitMailerSetting()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        ReflectionUtils::invokeMethod($initializer, '_initMailerSetting', array());
        $result = $this->getSettingService()->get('mailer');
        $this->assertArrayEquals(array(
            'enabled' => 0,
            'host' => 'smtp.exmail.qq.com',
            'port' => '25',
            'username' => 'user@example.com',
            'password' => '',
            'from' => 'user@example.com',
            'name' => '',
        ), $result);
    }

    public function testInitAdminUser()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        $fields = array(
            'email' => 'test@edusoho.com',
            'password' => 'test',
            'nickname' => 'testnickname'
        );
        $result = $initializer->initAdminUser($fields);

        $this->assertEquals('test@edusoho.com',$result['email']);
        $this->assertEquals('testnickname',$result['nickname']);
        $this->assertArrayEquals(array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN'), $result['roles']);
    }

    public function testInitCustom()
    {
        $output = new ConsoleOutput();
        $initializer = new \AppBundle\Common\SystemInitializer($output);

        $initializer->_initCustom();
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

}