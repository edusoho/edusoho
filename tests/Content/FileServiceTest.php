<?php
namespace Topxia\Service\Content\Tests;

use Biz\BaseTestCase;
use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

;

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
        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getUploadFileService()->uploadFile('tmp', $file);
        $this->assertTrue(file_exists($fileRecord['file']->getRealPath()));
        unlink($fileRecord['file']->getRealPath());
    }

    /**
     * @return FileService
     */
    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('Content:FileService');
    }
}
