<?php
namespace Topxia\Service\File\Tests;

use Mockery;
use Topxia\Common\ArrayToolkit;
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

    public function testGetConversationByTargetIdAndTargetType()
    {
        $members = array(
            array('id' => 1, 'nickname' => 'nickname1')
        );

        $this->createApiMock();

        $conversation1 = $this->getConversationService()->createConversation('conversation1', 'course', 1, $members);
        $conversation2 = $this->getConversationService()->createConversation('conversation2', 'classroom', 1, $members);

        $conversation = $this->getConversationService()->getConversationByTargetIdAndTargetType(1, 'course');
        var_dump($conversation);exit;
        $this->assertEquals('conversation1', $conversation['title']);
        $this->assertEquals('course', $conversation['targetType']);
        $this->assertEquals(1, $conversation['targetId']);

        $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($conversation['no'], 1);
        $this->assertEquals(1, $conversationMember['userId']);
        $this->assertEquals('course', $conversationMember['targetType']);
    }

    public function testCreateConversation()
    {
        $members = array(
            array('id' => 1, 'nickname' => 'username1'),
            array('id' => 2, 'nickname' => 'username2')
        );

        $this->createApiMock();

        $conversation = $this->getConversationService()->createConversation('testIm', 'course', '2', $members);

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $conversation['no']);
        $this->assertEquals('course', $conversation['targetType']);
        $this->assertEquals('2', $conversation['targetId']);

        $conversation1 = $this->getConversationService()->createConversation('testIm', 'private', '0', $members);
        $title         = join(ArrayToolkit::column($members, 'nickname'), '-').'的私聊';

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $conversation1['no']);
        $this->assertEquals('private', $conversation1['targetType']);
        $this->assertEquals('0', $conversation1['targetId']);
        $this->assertEquals($title, $conversation1['title']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateCloudConversation()
    {
        $this->createApiMock();

        $members = array(array('id' => 1, 'nickname' => 'nickname1'));
        $convNo  = $this->getConversationService()->createCloudConversation('conversation1', $members);

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $convNo);

        $convNo2 = $this->getConversationService()->createCloudConversation('conversation2', array());
    }

    /*public function testAddConversation()
    {
    $conversation = array('3b5db36d838e8252db2ebc170693db66', array())
    $this->getConversationService()->addConversation($conversation);
    }*/

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
    protected function createApiMock()
    {
        $api        = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('no' => '3b5db36d838e8252db2ebc170693db66'));
        $this->getConversationService()->setImApi($mockObject);
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
