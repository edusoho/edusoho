<?php

namespace Topxia\Service\Util\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Util\HTMLPurifierFactory;

class SystemUtilTest extends BaseTestCase
{

    public function test1()
    {
        $purifier = $this->createPurifier(true);

        $html1 = file_get_contents(__DIR__.'/test1.html');
        $html1 = $purifier->purify($html1);

        var_dump($html1);

    }

    private function createPurifier($trusted = false)
    {
        $config = array(
            'cacheDir' => $this->getServiceKernel()->getParameter('kernel.cache_dir').'/htmlpurifier'
        );

        $factory  = new HTMLPurifierFactory($config);
        return $factory->create($trusted);
    }
}