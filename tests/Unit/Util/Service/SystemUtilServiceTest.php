<?php

namespace Tests\Unit\Util\Service;

use Biz\BaseTestCase;

class SystemUtilServiceTest extends BaseTestCase
{
    public function testRemoveUnusedUploadFiles()
    {
        $result = $this->getSystemUtilService()->removeUnusedUploadFiles();
        $this->assertEquals(0, $result);

        $params = array(
            array(
                'functionName' => 'getCourseIdsWhereCourseHasDeleted',
                'returnValue' => array(
                    array('targetId' => 1),
                    array('targetId' => 2),
                ),
            ),
        );
        $this->mockBiz('Util:SystemUtilDao', $params);

        $params = array(
            array(
                'functionName' => 'searchFiles',
                'returnValue' => array(
                    array('id' => 1),
                    array('id' => 2),
                    array('id' => 3),
                ),
            ),
            array(
                'functionName' => 'deleteFile',
                'returnValue' => 1,
            ),
        );

        $this->mockBiz('File:UploadFileService', $params);

        $test = $this->getSystemUtilService()->removeUnusedUploadFiles();

        $this->assertEquals(3, $test);
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getSystemUtilService()
    {
        return $this->createService('Util:SystemUtilService');
    }
}
