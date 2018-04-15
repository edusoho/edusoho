<?php

namespace Tests\Unit\Mail;

use Biz\BaseTestCase;
use Biz\Mail\Template\EffectEmailResetPasswordTemplate;

class EffectEmailResetPasswordTemplateTest extends BaseTestCase
{
    public function testParse()
    {
        $siteName = 'EDUSOHO';
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('name' => $siteName),
            )
        ));
        $template = new EffectEmailResetPasswordTemplate(array());
        $template->setBiz($this->biz);

        $result = $template->parse(array('params' => array('nickname' => 'nickname', 'verifyurl' => '', 'sitename' => 'www.edusoho.com', 'siteurl' => '')));
        
        $this->assertEquals("重置您的{$siteName}帐号密码", $result['title']);
        $this->assertNotNull($result['body']);
    }
}
