<?php
namespace Topxia\Service\Util\Impl\Tests;

use Topxia\Service\Common\BaseTestCase;

class SystemUtilServiceImplTest extends BaseTestCase
{
    public function testRemoveUnusedUploadFiles()
    {
        $file = array(
            'hashId'     => 'courselesson/1/20151030034018-4rnonr.mp4',
            'targetId'   => 1,
            'targetType' => 'courselesson',
            'filename'   => '1.mp4',
            'ext'        => 'mp4',
            'size'       => 0,
            'storage'    => 'local',
            'usedCount'  => 0
        );

        $this->getUploadFileDao()->addFile($file);

        $test = $this->getSystemUtilService()->removeUnusedUploadFiles();

        $this->assertEquals(1, $test);

    }

    protected function getUploadFileDao()
    {
        return $this->getServiceKernel()->createDao('File.UploadFileDao');
    }

    protected function getSystemUtilService()
    {
        return $this->getServiceKernel()->createService('Util.SystemUtilService');
    }

}
