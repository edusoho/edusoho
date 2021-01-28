<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\PluginVersionToolkit;

class PluginVersionToolkitTest extends BaseTestCase
{
    public function testDependencyVersion()
    {
        $result = PluginVersionToolkit::dependencyVersion('Crm', '1.2.0');
        $this->assertTrue($result);

        $result = PluginVersionToolkit::dependencyVersion('Crm', '1.0.0');
        $this->assertTrue(!$result);

        $result = PluginVersionToolkit::dependencyVersion('testCode', '1.0.0');
        $this->assertTrue($result);
    }
}
