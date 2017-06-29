<?php

namespace Codeages\PluginBundle\Tests\System\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjectionCollector;

class SlotInjectionCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        $cacheDir = sys_get_temp_dir();

        $this->removeCache($cacheDir);

        $files = array(
            __DIR__.'/Fixtures/slot_1.yml',
            __DIR__.'/Fixtures/slot_2.yml',
            __DIR__.'/Fixtures/slot_3.yml',
        );

        $collector = new SlotInjectionCollector($files, $cacheDir, true);

        $injections = require $cacheDir.'/slot.php';
    }

    public function removeCache($cachDir)
    {
        if (file_exists($cachDir.'/slot.php')) {
            unlink($cachDir.'/slot.php');
        }

        if (file_exists($cachDir.'/slot.php.meta')) {
            unlink($cachDir.'/slot.php.meta');
        }
    }
}
