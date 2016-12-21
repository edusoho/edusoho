<?php
namespace Tests\Util;

use Biz\BaseTestCase;

class SystemUtilServiceImplTest extends BaseTestCase
{
    public function testRemoveUnusedUploadFiles()
    {
        $params = array(
            array(
                'functionName' => 'create',
                'runTimes'     => 1,
                'withParams'   => array(1),
                'returnValue'  => array(
                    'id'            => 1,
                    'storage'       => 'cloud',
                    'filename'      => 'test',
                    'createdUserId' => 1
                )
            )
        );
        $this->mockBiz('File:UploadFileDao', 'UploadFileDao', $params);

        $params = array(
            array(
                'functionName' => 'addFile',
                'runTimes'     => 1,
                'withParams'   => array(
                    'id'            => 1,
                    'storage'       => 'cloud',
                    'filename'      => 'test',
                    'createdUserId' => 1
                ),
                'returnValue'  => array(
                    'id'            => 1,
                    'storage'       => 'cloud',
                    'filename'      => 'test',
                    'createdUserId' => 1
                )
            )
        );
        $this->mockBiz('File:LocalFileImplementor', 'LocalFileImplementor', $params);
        $file = $this->getUploadFileService()->addFile('materiallib', 1);

        $test = $this->getSystemUtilService()->removeUnusedUploadFiles();

        $this->assertEquals(1, $test);

    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File:UploadFileService');
    }

    protected function getSystemUtilService()
    {
        return $this->getServiceKernel()->createService('Util:SystemUtilService');
    }

}
