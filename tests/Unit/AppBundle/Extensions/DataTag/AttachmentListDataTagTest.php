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
    public function testeGetArgumentError()
    {
        $dataTag = new AttachmentListDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $dataTag = new AttachmentListDataTag();
        $data = $dataTag->getData(array('targetType' => 'article', 'targetId' => 1));
        $this->assertEmpty($data);
    }

    private function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
