<?php
namespace Tests\Util;

use Biz\BaseTestCase;

class SystemUtilServiceImplTest extends BaseTestCase
{
    public function testRemoveUnusedUploadFiles()
    {
        $params = array(
            array(
                'functionName' => 'getCourseIdsWhereCourseHasDeleted',
                'returnValue'  => array(
                    array('targetId' => 1),
                    array('targetId' => 2)
                )
            )
        );
        $this->mockBiz('Util:SystemUtilDao', 'SystemUtilDao', $params);

        $params = array(
            array(
                'functionName' => 'searchFiles',
                'returnValue'  => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3)
                )
            ),
            array(
                'functionName' => 'deleteFile',
                'returnValue'  => 1
            )
        );
        
        $this->mockBiz('File:UploadFileService', 'UploadFileService', $params);

        $test = $this->getSystemUtilService()->removeUnusedUploadFiles();

        $this->assertEquals(3, $test);
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
