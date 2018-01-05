<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\AttachmentListDataTag;

class AttachmentListDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage 缺少参数，无法获取附件列表
     */
    public function testGetArgumentError()
    {
        $dataTag = new AttachmentListDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'findUseFilesByTargetTypeAndTargetIdAndType',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
        ));
        $dataTag = new AttachmentListDataTag();
        $data = $dataTag->getData(array('targetType' => 'article', 'targetId' => 1));

        $this->assertEquals(2, count($data));
        $this->assertEquals(1, $data[1]['id']);
        $this->assertEquals(2, $data[2]['id']);
    }

    private function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
