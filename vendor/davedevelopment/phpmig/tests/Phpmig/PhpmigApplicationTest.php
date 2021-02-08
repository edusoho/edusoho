<?php

namespace Phpmig\Console;

use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class PhpmigApplicationTest extends TestCase
{
    public function testAllCommandsRegistered()
    {
        $version = 'test';
        $commandNames = array('check', 'down', 'up', 'generate', 'init', 'migrate', 'rollback', 'status', 'up', 'redo');

        $app = new PhpmigApplication($version);

        foreach ($commandNames as $commandName) {
            $this->assertTrue($app->has($commandName));
        }

        $this->assertSame($version, $app->getVersion());
    }
}
