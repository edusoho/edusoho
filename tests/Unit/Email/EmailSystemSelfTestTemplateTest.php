<?php

namespace Tests\Unit\CloudFile;

use Biz\BaseTestCase;
use Biz\Mail\Template\EmailSystemSelfTestTemplate;

class EmailSystemSelfTestTemplateTest extends BaseTestCase
{
    public function testParse()
    {
        $template = new EmailSystemSelfTestTemplate();
        $template->setBiz($this->getBiz());
        $result = $template->parse(array(
            'params' => array('nickname' => 'test'),
        ));

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('body', $result);
    }
}
