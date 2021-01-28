<?php

namespace Tests\Unit\Mail;

use Biz\BaseTestCase;
use Biz\Mail\Template\EmailVerifyEmailTemplate;

class EmailVerifyEmailTemplateTest extends BaseTestCase
{
    public function testParse()
    {
        $siteName = 'EDUSOHO';
        $nickname = 'nickname';
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('name' => $siteName),
            ),
        ));
        $template = new EmailVerifyEmailTemplate(array());
        $template->setBiz($this->biz);

        $result = $template->parse(array('params' => array('nickname' => $nickname, 'verifyurl' => '', 'sitename' => 'www.edusoho.com', 'siteurl' => '')));

        $this->assertEquals("验证{$nickname}在{$siteName}的电子邮箱", $result['title']);
        $this->assertNotNull($result['body']);
    }
}
