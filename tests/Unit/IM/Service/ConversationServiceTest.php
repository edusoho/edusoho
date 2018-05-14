<?php

namespace Tests\Unit\IM\Service;

use Mockery;
use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;

class ConversationServiceTest extends BaseTestCase
{
    public function testGetConversationByMemberIds()
    {
        $data = array(
            'no' => 'abcdefg',
            'memberIds' => array(1, 2),
        );

        $added = $this->getConversationService()->addConversation($data);

        $getted = $this->getConversationService()->getConversationByMemberIds(array(2, 1));

        $this->assertEquals($added, $getted);
    }

    public function testGetConversationByConvNo()
    {
        $members = array(
            array('id' => 1, 'nickname' => 'username1'),
            array('id' => 2, 'nickname' => 'username2'),
        );

        $this->createApiMock('3b5db36d838e8252db2ebc170693db66');

        $conversation = $this->getConversationService()->createConversation('testIm', 'course', '2', $members);
        $conversation = $this->getConversationService()->getConversationByConvNo('3b5db36d838e8252db2ebc170693db66');

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $conversation['no']);
        $this->assertEquals('course', $conversation['targetType']);
        $this->assertEquals('2', $conversation['targetId']);
    }

    public function testGetConversationByTarget()
    {
        $members = array(
            array('id' => 1, 'nickname' => 'nickname1'),
        );

        $this->createApiMock('3b5db36d838e8252db2ebc170693db66');

        $this->getConversationService()->createConversation('conversation1', 'course', 1, $members);

        $this->createApiMock('8fdb36d838e8252db2ebc170693db89');
        $conversation2 = $this->getConversationService()->createConversation('conversation2', 'classroom', 1, $members);

        $conversation = $this->getConversationService()->getConversationByTarget(1, 'course');

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
            array('id' => 2, 'nickname' => 'username2'),
        );

        $this->createApiMock('3b5db36d838e8252db2ebc170693db66');

        $conversation = $this->getConversationService()->createConversation('testIm', 'course', '2', $members);

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $conversation['no']);
        $this->assertEquals('course', $conversation['targetType']);
        $this->assertEquals('2', $conversation['targetId']);

        $this->createApiMock('8fdb36d838e8252db2ebc170693db89');
        $conversation1 = $this->getConversationService()->createConversation('testIm', 'private', '0', $members);
        $title = join(ArrayToolkit::column($members, 'nickname'), '-').'的私聊';

        $this->assertEquals('8fdb36d838e8252db2ebc170693db89', $conversation1['no']);
        $this->assertEquals('private', $conversation1['targetType']);
        $this->assertEquals('0', $conversation1['targetId']);
        $this->assertEquals($title, $conversation1['title']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCreateCloudConversation()
    {
        $this->createApiMock('3b5db36d838e8252db2ebc170693db66');

        $members = array(array('id' => 1, 'nickname' => 'nickname1'));
        $convNo = $this->getConversationService()->createCloudConversation('conversation1', $members);

        $this->assertEquals('3b5db36d838e8252db2ebc170693db66', $convNo);

        $this->getConversationService()->createCloudConversation('conversation2', array());
    }

    public function testAddConversation()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );

        $conversation1 = $this->getConversationService()->addConversation($createConversation1);

        $this->assertEquals($createConversation1['title'], $conversation1['title']);
        $this->assertEquals(implode('|', $createConversation1['memberIds']), implode('|', $conversation1['memberIds']));

        $createConversation2 = array(
            'no' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'private',
            'title' => 'conversation1',
            'memberIds' => array('1', '2'),
        );

        $conversation2 = $this->getConversationService()->addConversation($createConversation2);
        $this->assertEquals($createConversation2['title'], $conversation2['title']);
        $this->assertEquals(implode('|', $createConversation2['memberIds']), implode('|', $conversation2['memberIds']));
    }

    public function testSearchConversations()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );
        $this->getConversationService()->addConversation($createConversation1);

        $createConversation2 = array(
            'no' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'title' => 'conversation2',
            'memberIds' => array(),
        );
        $this->getConversationService()->addConversation($createConversation2);

        $conversations = $this->getConversationService()->searchConversations(array('targetTypes' => array('course')), array('createdTime' => 'DESC'), 0, 1);

        $this->assertCount(1, $conversations);
        $this->assertEquals($createConversation1['targetType'], $conversations[0]['targetType']);
        $this->assertEquals($createConversation1['title'], $conversations[0]['title']);
        $this->assertEquals($createConversation1['no'], $conversations[0]['no']);
    }

    public function testSearchConversationCount()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );
        $this->getConversationService()->addConversation($createConversation1);

        $createConversation2 = array(
            'no' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'title' => 'conversation2',
            'memberIds' => array(),
        );
        $this->getConversationService()->addConversation($createConversation2);

        $count = $this->getConversationService()->searchConversationCount(array('targetTypes' => array('classroom')));

        $this->assertEquals(1, $count);
    }

    public function testDeleteConversationByTargetIdAndTargetType()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );
        $conversation1 = $this->getConversationService()->addConversation($createConversation1);

        $createConversation2 = array(
            'no' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'title' => 'conversation2',
            'memberIds' => array(),
        );
        $this->getConversationService()->addConversation($createConversation2);

        $this->getConversationService()->deleteConversationByTargetIdAndTargetType(1, 'course');

        $conversation = $this->getConversationService()->getConversation($conversation1['id']);

        $this->assertNull($conversation);
    }

    public function testGetMember()
    {
        $createMember = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $member = $this->getConversationService()->addMember($createMember);

        $member = $this->getConversationService()->getMember($member['id']);

        $this->assertEquals($createMember['convNo'], $member['convNo']);
        $this->assertEquals($createMember['targetType'], $member['targetType']);
    }

    public function testGetMemberByConvNoAndUserId()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $member1 = $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember2);

        $member = $this->getConversationService()->getMemberByConvNoAndUserId('3b5db36d838e8252db2ebc170693db66', 1);

        $this->assertEquals($member1['userId'], $member['userId']);
        $this->assertEquals($member1['convNo'], $member['convNo']);
    }

    public function testFindMembersByConvNo()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 2,
        );
        $this->getConversationService()->addMember($createMember2);

        $members = $this->getConversationService()->findMembersByConvNo('3b5db36d838e8252db2ebc170693db66');

        $this->assertCount(2, $members);
    }

    public function testFindMembersByUserIdAndTargetType()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember2);

        $convMembers = $this->getConversationService()->findMembersByUserIdAndTargetType(1, 'classroom');

        $this->assertCount(1, $convMembers);
    }

    public function testAddMember()
    {
        $createMember = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );

        $member = $this->getConversationService()->addMember($createMember);

        $this->assertEquals($createMember['convNo'], $member['convNo']);
        $this->assertEquals($createMember['targetType'], $member['targetType']);
    }

    public function testDeleteMember()
    {
        $createMember = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );

        $member = $this->getConversationService()->addMember($createMember);
        $this->getConversationService()->deleteMember($member['id']);

        $member = $this->getConversationService()->getMember($member['id']);

        $this->assertNull($member);
    }

    public function testDeleteMemberByConvNoAndUserId()
    {
        $createMember = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );

        $member = $this->getConversationService()->addMember($createMember);
        $this->getConversationService()->deleteMemberByConvNoAndUserId('3b5db36d838e8252db2ebc170693db66', 1);

        $member = $this->getConversationService()->getMember($member['id']);
        $this->assertNull($member);
    }

    public function testDeleteMembersByTargetIdAndTargetType()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 2,
        );
        $this->getConversationService()->addMember($createMember2);
        $this->getConversationService()->deleteMembersByTargetIdAndTargetType('1', 'course');

        $memberCount = $this->getConversationService()->searchMemberCount(array('targetId' => 1, 'targetType' => 'course'));

        $this->assertEquals(0, $memberCount);
    }

    public function testJoinConversation()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );
        $conversation1 = $this->getConversationService()->addConversation($createConversation1);

        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getConversationService()->setImApi($mockObject);

        $convMember = $this->getConversationService()->joinConversation($conversation1['no'], 1);

        $this->assertEquals($conversation1['targetId'], $convMember['targetId']);
        $this->assertEquals($conversation1['targetType'], $convMember['targetType']);
        $this->assertEquals(1, $convMember['userId']);
    }

    public function testQuitConversation()
    {
        $createConversation1 = array(
            'no' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'title' => 'conversation1',
            'memberIds' => array(),
        );
        $conversation1 = $this->getConversationService()->addConversation($createConversation1);

        $createMember1 = array(
            'convNo' => $conversation1['no'],
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );
        $member1 = $this->getConversationService()->addMember($createMember1);

        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('delete')->times(1)->andReturn(array('success' => true));
        $this->getConversationService()->setImApi($mockObject);

        $this->getConversationService()->quitConversation($conversation1['no'], 1);

        $convMember = $this->getConversationService()->getMember($member1['id']);

        $this->assertNull($convMember);
    }

    public function testAddConversationMember()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getConversationService()->setImApi($mockObject);

        $members = array(
            array('id' => 1, 'nickname' => 'nickname1'),
            array('id' => 2, 'nickname' => 'nickname2'),
        );
        $result = $this->getConversationService()->addConversationMember('3b5db36d838e8252db2ebc170693db66', $members);

        $this->assertTrue($result);
    }

    public function testRemoveConversationMember()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('delete')->times(1)->andReturn(array('success' => true));
        $this->getConversationService()->setImApi($mockObject);

        $result = $this->getConversationService()->removeConversationMember('3b5db36d838e8252db2ebc170693db66', 1);

        $this->assertTrue($result);
    }

    public function testIsImMemberFull()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('offline' => array(array('id' => 1)), 'online' => array(array('id' => 2))));
        $this->getConversationService()->setImApi($mockObject);

        $result = $this->getConversationService()->isImMemberFull('3b5db36d838e8252db2ebc170693db66', 2);

        $this->assertTrue($result);
    }

    public function testsearchMembers()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );

        $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'userId' => 1,
        );

        $this->getConversationService()->addMember($createMember2);

        $members = $this->getConversationService()->searchMembers(array('targetTypes' => array('classroom'), 'userId' => 1), array('createdTime' => 'DESC'), 0, 1);
        $this->assertEquals($createMember2['targetType'], $members[0]['targetType']);
    }

    public function testsearchMemberCount()
    {
        $createMember1 = array(
            'convNo' => '3b5db36d838e8252db2ebc170693db66',
            'targetId' => 1,
            'targetType' => 'course',
            'userId' => 1,
        );

        $this->getConversationService()->addMember($createMember1);

        $createMember2 = array(
            'convNo' => '8fdb36d838e8252db2ebc170693db89',
            'targetId' => 1,
            'targetType' => 'classroom',
            'userId' => 1,
        );
        $this->getConversationService()->addMember($createMember2);

        $count = $this->getConversationService()->searchMemberCount(array('targetTypes' => array('classroom'), 'userId' => 1));

        $this->assertEquals(1, $count);
    }

    protected function createApiMock($no)
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('no' => $no));
        $this->getConversationService()->setImApi($mockObject);
    }

    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }
}
