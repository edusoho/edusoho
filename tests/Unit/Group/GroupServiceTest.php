<?php

namespace Tests\Unit\Group;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Group\Service\GroupService;

class GroupServiceTest extends BaseTestCase
{
    public function testAddGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $this->assertEquals(1, $group['id']);

        $this->assertEquals($textGroup['title'], $group['title']);

        $this->assertEquals($textGroup['about'], $group['about']);

        $this->assertEquals('open', $group['status']);
    }

    public function testSearchGroups()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $results = $this->getGroupService()->searchGroups(array('userId' => $user['id']), array(), 0, 10);
        $this->assertEquals($group, reset($results));
    }

    /**
     * @group current
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddGroupWithEmptyTitle()
    {
        $textGroup = array(
        );
        $user = $this->createUser();
        $group = $this->getGroupService()->addGroup($user, $textGroup);
    }

    public function testGetGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $group = $this->getGroupService()->getGroup($group['id']);

        $this->assertTrue(is_array($group));

        $this->assertEquals($textGroup['title'], $group['title']);

        $group = $this->getGroupService()->getGroup('999');

        $this->assertEquals(null, $group);
    }

    public function testUpdateGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );

        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $fields = array(
            'title' => 'textgroup22222',
            'about' => '123456789test',
        );

        $group = $this->getGroupService()->updateGroup($group['id'], $fields);

        $this->assertEquals($fields['title'], $group['title']);

        $group = $this->getGroupService()->updateGroup('999', $fields);

        $this->assertEquals(null, $group);
    }

    public function testCloseGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $group = $this->getGroupService()->closeGroup($group['id']);

        $this->assertEquals('close', $group['status']);
    }

    public function testOpenGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $group = $this->getGroupService()->closeGroup($group['id']);

        $group = $this->getGroupService()->openGroup($group['id']);

        $this->assertEquals('open', $group['status']);
    }

    public function testGetGroupsByIds()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup1',
        );
        $textGroup2 = array(
            'title' => 'textgroup2',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $group2 = $this->getGroupService()->addGroup($user, $textGroup2);

        $ids = array($group1['id'], $group2['id']);
        $groups = $this->getGroupService()->GetGroupsByIds($ids);

        $this->assertTrue(is_array($groups));

        $this->assertEquals($textGroup1['title'], $groups[$group1['id']]['title']);

        $this->assertEquals($textGroup2['title'], $groups[$group2['id']]['title']);

        $ids = array(999, 9999);
        $groups = $this->getGroupService()->GetGroupsByIds($ids);

        $this->assertEquals(array(), $groups);
    }

    public function testSearchGroupsCount()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup1',
        );
        $textGroup2 = array(
            'title' => 'textgroup2',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $group2 = $this->getGroupService()->addGroup($user, $textGroup2);

        $count = $this->getGroupService()->searchGroupsCount(array('title' => $textGroup1['title']));

        $this->assertEquals(1, $count);

        $count = $this->getGroupService()->searchGroupsCount(array('title' => 'text'));

        $this->assertEquals(2, $count);

        $count = $this->getGroupService()->searchGroupsCount(array('title' => '???'));

        $this->assertEquals(0, $count);
    }

    public function testJoinGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);
    }

    public function testExistGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);

        $this->getGroupService()->exitGroup($user1, $group['id']);

        $afterGet = $this->getGroupService()->getMemberByGroupIdAndUserId($group['id'], $user1['id']);
        $this->assertEmpty($afterGet);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testExistGroupWithEmptyGroup()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);

        $this->getGroupService()->exitGroup($user1, $group['id'] + 1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testExistGroupWithEmptyUser()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);

        $user1['id'] = 100;
        $this->getGroupService()->exitGroup($user1, $group['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testJoinGroupWithJoinGroupExist()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);

        $this->getGroupService()->joinGroup($user1, $group['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testJoinGroupWithErrorId()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $user1 = $this->createUser1();
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->getGroupService()->joinGroup($user1, 999);
    }

    public function testIsAdmin()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $user1 = $this->createUser1();
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $bool = $this->getGroupService()->isAdmin($group['id'], $user1['id']);
        $this->assertFalse($bool);
    }

    public function testPrepareGroupConditions()
    {
        $user = $this->createUser();

        $conditions = ReflectionUtils::invokeMethod($this->getGroupService(), 'prepareGroupConditions', array(
            array(
                'ownerName' => $user['nickname'],
                'status' => 'created',
            ),
        ));

        $this->assertEquals('created', $conditions['status']);
        $this->assertEquals($user['id'], $conditions['ownerId']);

        $conditions = ReflectionUtils::invokeMethod($this->getGroupService(), 'prepareGroupConditions', array(
            array(
                'ownerName' => $user['nickname'].'123',
                'status' => '',
            ),
        ));

        $this->assertFalse(isset($conditions['status']));
        $this->assertEquals(0, $conditions['ownerId']);
    }

    public function testFindGroupsByUserId()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup1',
        );
        $textGroup2 = array(
            'title' => 'textgroup2',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $group2 = $this->getGroupService()->addGroup($user, $textGroup2);

        $groups = $this->getGroupService()->findGroupsByUserId($user['id']);

        $this->assertTrue(is_array($groups));

        $this->assertEquals($textGroup1['title'], $groups[0]['title']);

        $this->assertEquals($textGroup2['title'], $groups[1]['title']);

        $groups = $this->getGroupService()->findGroupsByUserId(999);

        $this->assertEquals(array(), $groups);
    }

    public function testFindGroupByTitle()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup1',
        );
        $textGroup2 = array(
            'title' => 'textgroup2',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $group2 = $this->getGroupService()->addGroup($user, $textGroup2);

        $groups = $this->getGroupService()->findGroupBytitle('textgroup1');

        $this->assertTrue(is_array($groups));

        $this->assertEquals($textGroup1['title'], $groups[0]['title']);

        $groups = $this->getGroupService()->findGroupBytitle('textgroup11111111  ');

        $this->assertEquals(array(), $groups);
    }

    public function testSearchMembers()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup1',
        );
        $textGroup2 = array(
            'title' => 'textgroup2',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $group2 = $this->getGroupService()->addGroup($user, $textGroup2);

        $member = $this->getGroupService()->searchMembers(array('groupId' => $group1['id']), array('createdTime' => 'desc'), 0, 1);
        $this->assertEquals($user['id'], $member[0]['userId']);
        $this->assertEquals('owner', $member[0]['role']);
    }

    public function testSearchMembersCount()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $user1 = $this->createUser1();
        $this->getGroupService()->joinGroup($user1, $group1['id']);
        $count = $this->getGroupService()->getMembersCountByGroupId($group1['id']);

        $this->assertEquals(2, $count);

        $count = $this->getGroupService()->countMembers(array('groupId' => $group1['id']));
        $this->assertEquals(2, $count);

        $count = $this->getGroupService()->getMembersCountByGroupId(999);

        $this->assertEquals(0, $count);
    }

    public function testUpdateMember()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $groupMember = $this->getGroupService()->joinGroup($user1, $group['id']);

        $this->assertTrue(is_array($groupMember));

        $this->assertEquals($user1['id'], $groupMember['userId']);

        $this->assertEquals('member', $groupMember['role']);

        $this->assertEquals($group['id'], $groupMember['groupId']);

        $updateResult = $this->getGroupService()->updateMember($groupMember['id'], array(
            'postNum' => 10,
        ));

        $this->assertEquals(10, $updateResult['postNum']);
    }

    public function testAddOwner()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);

        $user1 = $this->createUser1();
        $member = $this->getGroupService()->addOwner($group['id'], $user1['id']);
        $this->assertEquals('owner', $member['role']);

        $getGroup = $this->getGroupService()->getGroup($group['id']);
        $this->assertEquals(2, $getGroup['memberNum']);
    }

    public function testIsOwner()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $status = $this->getGroupService()->isOwner($group1['id'], $user['id']);

        $this->assertTrue($status);

        $status = $this->getGroupService()->isOwner($group1['id'], 999);

        $this->assertFalse($status);
    }

    public function testIsMember()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $user1 = $this->createUser1();
        $status = $this->getGroupService()->isMember($group1['id'], $user1['id']);

        $this->assertFalse($status);

        $this->getGroupService()->joinGroup($user1, $group1['id']);

        $status = $this->getGroupService()->isMember($group1['id'], $user1['id']);

        $this->assertTrue($status);
    }

    public function testChangeGroupImg()
    {
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'getFilesByIds',
                    'withParams' => array(array(1, 2)),
                    'returnValue' => array(
                        array('id' => 1, 'uri' => '/files/1.jpg'),
                        array('id' => 2, 'uri' => '/files/3.jpg'),
                    ),
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                ),
            )
        );

        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);

        $member = $this->getGroupService()->changeGroupImg($group1['id'], 'logo', array(
            array(
                'type' => 'logo',
                'id' => 1,
            ),
            array(
                'type' => 'backgroundLogo',
                'id' => 2,
            ),
        ));

        $this->assertEquals('/files/1.jpg', $member['logo']);

        // deleteOldAvatar
        $this->getGroupService()->changeGroupImg($group1['id'], 'logo', array(
            array(
                'type' => 'logo',
                'id' => 1,
            ),
            array(
                'type' => 'backgroundLogo',
                'id' => 2,
            ),
        ));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testChangeGroupImgWithErrorType()
    {
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'getFilesByIds',
                    'withParams' => array(array(1, 2)),
                    'returnValue' => array(
                        array('id' => 1, 'uri' => '/files/1.jpg'),
                        array('id' => 2, 'uri' => '/files/3.jpg'),
                    ),
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                ),
            )
        );

        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);

        $member = $this->getGroupService()->changeGroupImg($group1['id'], 'errorType', array(
            array(
                'type' => 'logo',
                'id' => 1,
            ),
            array(
                'type' => 'backgroundLogo',
                'id' => 2,
            ),
        ));

        $this->assertEquals('/files/1.jpg', $member['logo']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testChangeGroupImgWithGroupNonExist()
    {
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'getFilesByIds',
                    'withParams' => array(array(1, 2)),
                    'returnValue' => array(
                        array('id' => 1, 'uri' => '/files/1.jpg'),
                        array('id' => 2, 'uri' => '/files/3.jpg'),
                    ),
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                ),
            )
        );

        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);

        $member = $this->getGroupService()->changeGroupImg($group1['id'] + 1, 'logo', array(
            array(
                'type' => 'logo',
                'id' => 1,
            ),
            array(
                'type' => 'backgroundLogo',
                'id' => 2,
            ),
        ));

        $this->assertEquals('/files/1.jpg', $member['logo']);
    }

    public function testGetMembersCountByGroupId()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $user1 = $this->createUser1();
        $this->getGroupService()->joinGroup($user1, $group1['id']);
        $count = $this->getGroupService()->getMembersCountByGroupId($group1['id']);

        $this->assertEquals(2, $count);

        $count = $this->getGroupService()->getMembersCountByGroupId(999);

        $this->assertEquals(0, $count);
    }

    public function testDeleteMemberByGroupIdAndUserId()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);
        $user1 = $this->createUser1();
        $this->getGroupService()->joinGroup($user1, $group1['id']);

        $this->getGroupService()->deleteMemberByGroupIdAndUserId($group1['id'], $user1['id']);

        $member = $this->getGroupService()->getMemberByGroupIdAndUserId($group1['id'], $user1['id']);

        $this->assertEmpty($member);
    }

    public function testWaveGroup()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);

        $this->getGroupService()->waveGroup($group1['id'], 'postNum', +1);
        $this->getGroupService()->waveGroup($group1['id'], 'threadNum', +1);
        $this->getGroupService()->waveGroup($group1['id'], 'memberNum', +1);

        $group = $this->getGroupService()->getGroup($group1['id']);

        $this->assertEquals(1, $group['postNum']);
        $this->assertEquals(1, $group['threadNum']);
        $this->assertEquals(2, $group['memberNum']);
    }

    /**
     * @group current
     */
    public function testWaveMember()
    {
        $user = $this->createUser();
        $textGroup1 = array(
            'title' => 'textgroup',
        );
        $group1 = $this->getGroupService()->addGroup($user, $textGroup1);

        $this->getGroupService()->waveMember($group1['id'], $user['id'], 'postNum', +10);
        $this->getGroupService()->waveMember($group1['id'], $user['id'], 'threadNum', +10);

        $member = $this->getGroupService()->getMemberByGroupIdAndUserId($group1['id'], $user['id']);

        $this->assertEquals(10, $member['postNum']);
        $this->assertEquals(10, $member['threadNum']);
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';

        return $this->getUserService()->register($user);
    }

    protected function createUser1()
    {
        $user = array();
        $user['email'] = 'user1@user1.com';
        $user['nickname'] = 'user1';
        $user['password'] = 'user1';

        return $this->getUserService()->register($user);
    }
}
