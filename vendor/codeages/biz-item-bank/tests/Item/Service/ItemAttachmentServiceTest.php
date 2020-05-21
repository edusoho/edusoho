<?php

namespace Tests\Item\Service;

use Codeages\Biz\ItemBank\Item\Dao\AttachmentDao;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Tests\IntegrationTestCase;

class AttachmentServiceTest extends IntegrationTestCase
{
    public function testCreateAttachment()
    {
        $attachment = [
            'global_id' => 'qwert',
            'hash_id' => 'asdff',
            'target_id' => 1,
            'target_type' => 'question',
            'module' => 'stem',
            'file_name' => '测试.docx',
            'ext' => 'docx',
            'size' => 100,
            'status' => 'finish',
            'file_type' => 'document',
            'created_user_id' => 1,
            'convert_status' => 'success',
        ];
        $result = $this->getAttachmentService()->createAttachment($attachment);

        $this->assertEquals('question', $result['target_type']);
        $this->assertEquals(1, $result['target_id']);
    }

    public function testGetAttachment()
    {
        $attachment = $this->mockAttachment(['file_name' => 'test.docx']);
        $result = $this->getAttachmentService()->getAttachment($attachment['id']);

        $this->assertEquals($attachment['file_name'], $result['file_name']);
    }

    public function testGetAttachmentByGlobalId()
    {
        $this->mockAttachment(['global_id' => 'test', 'file_name' => 'doc.docx']);
        $result = $this->getAttachmentService()->getAttachmentByGlobalId('test');

        $this->assertEquals('doc.docx', $result['file_name']);
    }

    public function testFindAttachmentsByTargetIdAndTargetType()
    {
        $this->mockAttachment(['target_id' => 2, 'file_name' => 'doc.docx']);
        $this->mockAttachment(['target_id' => 1, 'file_name' => 'test.docx']);

        $result = $this->getAttachmentService()->findAttachmentsByTargetIdAndTargetType(1, 'question');

        $this->assertCount(1, $result);
        $this->assertEquals('test.docx', $result[0]['file_name']);
    }

    public function testFindAttachmentsByTargetIdsAndTargetType()
    {
        $this->mockAttachment(['target_id' => 2, 'file_name' => 'doc.docx']);
        $this->mockAttachment(['target_id' => 1, 'file_name' => 'test.docx']);

        $result = $this->getAttachmentService()->findAttachmentsByTargetIdsAndTargetType([1], 'question');
        $this->assertCount(1, $result);
        $this->assertEquals('test.docx', $result[0]['file_name']);
    }

    public function testUpdateAttachment()
    {
        $attachment = $this->mockAttachment(['target_id' => 1, 'file_name' => 'test.docx']);
        $result = $this->getAttachmentService()->updateAttachment($attachment['id'], ['target_id' => 2, 'target_type' => 'item']);

        $this->assertEquals(2, $result['target_id']);
        $this->assertEquals('item', $result['target_type']);
    }

    public function testFinishUpload()
    {
        $attachment = $this->mockAttachment(['status' => 'uploading']);
        $result = $this->getAttachmentService()->finishUpload($attachment['id']);

        $this->assertEquals('finish', $result['status']);
    }

    public function testDeleteAttachment()
    {
        $attachment = $this->mockAttachment();
        $this->getAttachmentService()->deleteAttachment($attachment['id']);
        $result = $this->getAttachmentDao()->get($attachment['id']);

        $this->assertEmpty($result);
    }

    public function testBatchDeleteAttachment()
    {
        $attachment = $this->mockAttachment(['target_id' => 1, 'file_name' => 'doc.docx']);
        $this->mockAttachment(['target_id' => 1, 'file_name' => 'test.docx']);
        $this->getAttachmentService()->batchDeleteAttachment(['target_ids' => [1]]);
        $result = $this->getAttachmentDao()->get($attachment['id']);

        $this->assertEmpty($result);
    }

    public function testMakeToken()
    {
        $user = [
            'id' => 1,
            'uuid' => 'test',
        ];
        $token = $this->getAttachmentService()->makeToken($user, 'testAccessKey', 'testSecretKey');

        $this->assertEquals(196, strlen($token));
    }

    public function testParseToken()
    {
        $user = [
            'id' => 1,
            'uuid' => 'test',
        ];
        $token = $this->getAttachmentService()->makeToken($user, 'testAccessKey', 'testSecretKey');
        $result = $this->getAttachmentService()->parseToken($token, 'testAccessKey', 'testSecretKey');

        $this->assertEquals('test', $result['uuid']);
    }

    protected function mockAttachment($attachment = [])
    {
        $default = [
            'global_id' => 'qwert',
            'hash_id' => 'asdff',
            'target_id' => 1,
            'target_type' => 'question',
            'module' => 'stem',
            'file_name' => '测试.docx',
            'ext' => 'docx',
            'size' => 100,
            'status' => 'finish',
            'file_type' => 'document',
            'created_user_id' => 1,
            'convert_status' => 'success',
        ];
        $attachment = array_merge($default, $attachment);

        return $this->getAttachmentDao()->create($attachment);
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }

    /**
     * @return AttachmentDao
     */
    protected function getAttachmentDao()
    {
        return $this->biz->dao('ItemBank:Item:AttachmentDao');
    }
}
