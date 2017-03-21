<?php

namespace Topxia\Service\Util\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Util\HTMLPurifier;

class HTMLPurifierTest extends BaseTestCase
{
    public function testPurifierWithTrusted()
    {
        $purifier = new HTMLPurifier(array(
            'cacheDir' => '/tmp/html',
            'safeIframeDomains' => array(
                'www.edusoho.com',
                'player.youku.com',
            ),
        ));

        $html = $purifier->purify(file_get_contents(__DIR__.'/test1.html'), true);

        $this->assertContains('<iframe', $html);
        $this->assertContains('<embed', $html);
        $this->assertContains('www.edusoho.com', $html);
        $this->assertContains('player.youku.com', $html);

        $purifier = new HTMLPurifier(array(
            'cacheDir' => '/tmp/html',
            'safeIframeDomains' => array(
                'www.edusoho.com',
            ),
        ));

        $html = $purifier->purify(file_get_contents(__DIR__.'/test1.html'), true);

        $this->assertContains('<iframe', $html);
        $this->assertContains('<embed', $html);
        $this->assertContains('www.edusoho.com', $html);
        $this->assertNotContains('player.youku.com', $html);
    }

    public function testPurifierWithNotTrusted()
    {
        $purifier = new HTMLPurifier(array(
            'cacheDir' => '/tmp/html',
        ));

        $html = $purifier->purify(file_get_contents(__DIR__.'/test1.html'));

        $this->assertNotContains('<iframe', $html);
        $this->assertNotContains('<embed', $html);
    }
}
