<?php
namespace Topxia\Service\File\Tests;

use Mockery;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class ConversationServiceTest extends BaseTestCase
{
    public function testGetConversationByMemberIds()
    {
        $data = array(
            'no'        => 'abcdefg',
            'memberIds' => array(1, 2)
        );

        $added = $this->getConversationService()->addConversation($data);

        $getted = $this->getConversationService()->getConversationByMemberIds(array(2, 1));

        $this->assertEquals($added, $getted);
    }

    public function testCreateConversation()
    {
        $members = array(
            array('id' => 1, 'nickname' => 'username1'),
            array('id' => 2, 'nickname' => 'username2')
        );

        $api        = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('no' => '3b5db36d838e8252db2ebc170693db66'));
        $this->getConversationService()->setImApi($mockObject);

        $conversation = $this->getConversationService()->createConversation('testIm', 'course', '2', $members);

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $conversation['no']);
        $this->assertEquals('course', $conversation['targetType']);
        $this->assertEquals('2', $conversation['targetId']);
    }

    /*public function testGetMemberByConvNoAndUserId()
    {
    $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $userId);
    }

    public function testFindMembersByConvNo()
    {
    $this->getConversationService()->findMembersByConvNo($convNo);
    }

    public function testAddMember()
    {
    $this->getConversationService()->addMember($member);
    }

    public function testDeleteMember()
    {
    $this->getConversationService()->deleteMember($id);
    }

    public function testDeleteMemberByConvNoAndUserId()
    {
    $this->getConversationService()->deleteMemberByConvNoAndUserId($convNo, $userId);
    }

    public function testAddConversationMember()
    {
    $this->getConversationService()->addConversationMember($convNo, $userId, $nickname);
    }

    public function testCreateCloudConversation()
    {
    $this->getConversationService()->createCloudConversation();
    }

    public function testIsImMemberFull()
    {

    $this->getConversationService()->isImMemberFull($convNo);
    }*/

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
