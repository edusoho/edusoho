<?php

namespace Topxia\Service\Util\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Util\HTMLPurifier;

class SystemUtilTest extends BaseTestCase
{

    public function test1()
    {
        $purifier = new HTMLPurifier(array(
            'cacheDir' => '/tmp/html',
        ));

        $html = $purifier->purify(file_get_contents(__DIR__.'/test1.html'), true);

        var_dump($html);
        // var_dump($html1);

    }
}