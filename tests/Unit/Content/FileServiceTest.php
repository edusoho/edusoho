<?php

namespace Tests\Unit\Content;

use Biz\BaseTestCase;
use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileServiceTest extends BaseTestCase
{
    public function testGetFile()
    {
        $this->assertNull(null);
    }

    public function testUploadFile()
    {
        $sourceFile = __DIR__.'/Fixtures/test.gif';
        $testFile = __DIR__.'/Fixtures/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getFileService()->uploadFile('tmp', $file);
        $this->assertTrue(file_exists($fileRecord['file']->getRealPath()));
        unlink($fileRecord['file']->getRealPath());
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
