<?php

namespace Tests\Unit\Mail;

use Biz\BaseTestCase;
use Biz\Mail\Template\EmailRegistrationTemplate;

class EmailRegistrationTemplateTest extends BaseTestCase
{
    public function testParse()
    {
        $emailTitle = '请激活你的帐号 完成注册';
        $emailBody = ' 验证邮箱内容';
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('email_activation_title' => $emailTitle, 'auth.email_activation_body' => $emailBody),
            )
        ));
        $template = new EmailRegistrationTemplate(array());
        $template->setBiz($this->biz);

        $result = $template->parse(array('params' => array('nickname' => 'nickname', 'verifyurl' => '', 'sitename' => 'www.edusoho.com', 'siteurl' => '')));
        
        $this->assertEquals($emailTitle, $result['title']);
        $this->assertNotNull($result['body']);
    }
}
