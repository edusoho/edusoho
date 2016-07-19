<?php


namespace Topxia\Service\Attachment\Tests;


use Topxia\Service\Common\BaseTestCase;

class AttachmentServiceTest extends BaseTestCase
{
    public function testCreates()
    {
        $attachments = $this->generateAttachments(5);
        $this->getService()->creates($attachments);

        $attachments = $this->getService()->findByTargetTypeAndTargetId('test', 1);
        $this->assertEquals(count($attachments), 5);
    }

    public function testCreate()
    {
        $originAttachment = $this->generateAttachment($this->generateFileIds());
        $remoteAttachment = $this->getService()->create($originAttachment);

        foreach (array('targetType', 'targetId', 'fileId') as $key) {
            $this->assertEquals($originAttachment[$key], $remoteAttachment[$key]);
        }
    }

    public function testFindByTargetTypeAndTargetId()
    {
        $service = $this->getService();
        $attachment = $this->generateAttachment($this->generateFileIds());
        $attachment = $service->create($attachment);
        $attachments = $service->findByTargetTypeAndTargetId($attachment['targetType'], $attachment['targetId']);
        $this->assertArrayEquals(array($attachment), $attachments);
    }

    public function testGetAttachment()
    {
        $originAttachment = $this->generateAttachment($this->generateFileIds());
        $remoteAttachment = $this->getService()->create($originAttachment);

        $get = $this->getService()->get($remoteAttachment['id']);
        $this->assertArrayEquals($remoteAttachment, $get);
    }

    protected function generateAttachment($fileId)
    {
        $attachment = array(
            'fileId' => $fileId,
            'targetType' => 'test',
            'targetId'   => 1
        );
        return $attachment;
    }

    protected function generateAttachments($num)
    {
        $self = $this;
        $attachments = array_map(function($fileId) use($self){
            return $self->generateAttachment($fileId);
        }, $this->generateFileIds($num));
        return $attachments;
    }

    protected function generateFileIds($num=1)
    {
        if($num == 1) {
            return 1;
        }else{
            return range(1, $num);
        }
    }

    protected function getService()
    {
        return $this->getServiceKernel()->createService('Attachment.AttachmentService');
    }

}