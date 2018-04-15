<?php

namespace Tests\Unit\Util;

use Biz\BaseTestCase;
use Biz\Util\FileUtil;

class FileUtilTest extends BaseTestCase
{
    public function tearDown()
    {
        $folderNames = array('testFolder', 'testFolder2');
        foreach ($folderNames as $folderName) {
            $folderPath = $this->biz['kernel.root_dir'].'/data/'.$folderName;
            if (file_exists($folderPath)) {
                FileUtil::emptyDir($folderPath, true);
            }
        }
    }

    public function testEmptyDir()
    {
        $folderPath = $this->biz['kernel.root_dir'].'/data/testFolder';
        mkdir($folderPath, 0777);
        file_put_contents($folderPath.'/test', 1111);

        $this->assertTrue(file_exists($folderPath));
        $this->assertTrue(file_exists($folderPath.'/test'));

        FileUtil::emptyDir($folderPath, false);

        $this->assertTrue(file_exists($folderPath));
        $this->assertFalse(file_exists($folderPath.'/test'));

        file_put_contents($folderPath.'/test', 1111);
        FileUtil::emptyDir($folderPath, true);

        $this->assertFalse(file_exists($folderPath));
        $this->assertFalse(file_exists($folderPath.'/test'));
    }

    public function testDeepCopy()
    {
        $folderPath = $this->biz['kernel.root_dir'].'/data/testFolder';
        mkdir($folderPath, 0777);
        file_put_contents($folderPath.'/test', 1111);

        $copiedFolder = $this->biz['kernel.root_dir'].'/data/testFolder2';
        $copiedFile = $copiedFolder.'/test';
        FileUtil::deepCopy($folderPath, $copiedFolder);

        $this->assertTrue(file_exists($copiedFolder));
        $this->assertTrue(file_exists($copiedFile));
    }
}
