<?php

namespace Tests\Unit\Thread\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Thread\Service\ThreadService;
use Biz\User\CurrentUser;

class ThreadServiceTest extends BaseTestCase
{
    public function testGetThread()
    {
        $thread = $this->createProtectThread();

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    public function testCreateThread()
    {
        $thread = $this->createProtectThread();

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     * @expectedExceptionMessage exception.thread.title_required
     */
    public function testCreatedThreadWithNoneTitleException()
    {
        $thread = $this->createProtectThread([], ['title']);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     * @expectedExceptionMessage exception.thread.content_required
     */
    public function testCreatedThreadWithNoneContentException()
    {
        $thread = $this->createProtectThread([], ['content']);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     * @expectedExceptionMessage exception.thread.targetid_required
     */
    public function testCreatedThreadWithNoneTargetIdException()
    {
        $thread = $this->createProtectThread([], ['targetId']);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreatedThreadWithNoneTypeException()
    {
        $thread = $this->createProtectThread(['type' => 'unknown']);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    public function testCreateThreadWithEventType()
    {
        $thread = $this->createProtectThread([
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
        ]);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
    }

    public function testCreateThreadWithAts()
    {
        $currentUser = $this->getCurrentUser();
        $this->createUser();
        $newUser = $this->createUser(['email' => 'newUser@user.com', 'nickname' => 'newUser']);
        $thread = $this->createProtectThread([
            'content' => '@user @newUser @admin',
            'userId' => $currentUser['id'],
        ]);

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread['id'], $threadNew['id']);
        $this->assertContains($newUser['id'], $threadNew['ats']);
    }

    public function testUpdateThread()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread = $this->createProtectThread();

        $fields = [
            'title' => 'title2',
            'content' => 'hello123',
            'startTime' => 'now',
        ];
        $threadUpdate = $this->getThreadService()->updateThread($thread['id'], $fields);

        $this->assertEquals($fields['title'], $threadUpdate['title']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testUpdateThreadWithEmptyThread()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread = $this->createProtectThread();

        $fields = [
            'title' => 'title2',
            'content' => 'hello123',
            'startTime' => time(),
        ];
        $this->getThreadService()->updateThread($thread['id'] + 1, $fields);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUpdateThreadWithEmptyFields()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread = $this->createProtectThread();

        $fields = [];
        $this->getThreadService()->updateThread($thread['id'], $fields);
    }

    public function testDeleteThread()
    {
        $thread = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->getThreadService()->deleteThread($thread['id']);

        $foundThread = $this->getThreadService()->getThread($thread['id']);
        $foundPost = $this->getThreadService()->getPost($createdPost['id']);

        $this->assertNull($foundThread);
        $this->assertNull($foundPost);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testDeleteThreadWithThreadNoneExist()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->deleteThread($thread['id'] + 1);
    }

    public function testDeleteThreadWithEventType()
    {
        $thread = $this->createProtectThread([
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
        ]);
        $createdPost = $this->createTestPost($thread);

        $this->getThreadService()->deleteThread($thread['id']);

        $foundThread = $this->getThreadService()->getThread($thread['id']);
        $foundPost = $this->getThreadService()->getPost($createdPost['id']);

        $this->assertNull($foundThread);
        $this->assertNull($foundPost);
    }

    public function testSetThreadSticky()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadSticky($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $result['sticky']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testSetThreadStickyWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadSticky($thread['id'] + 1);
    }

    public function testCancelThreadSticky()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->setThreadSticky($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['sticky']);

        $this->getThreadService()->cancelThreadSticky($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(0, $result['sticky']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCancelThreadStickyWithThreadNonExist()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->cancelThreadSticky($thread['id'] + 1);
    }

    public function testSetThreadNice()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadNice($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $result['nice']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testSetThreadNiceWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadNice($thread['id'] + 1);
    }

    public function testCancelThreadNice()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->setThreadNice($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['nice']);

        $this->getThreadService()->cancelThreadNice($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(0, $result['nice']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCancelThreadNiceWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->cancelThreadNice($thread['id'] + 1);
    }

    public function testSetThreadSolved()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $result['solved']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testSetThreadSolvedWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadSolved($thread['id'] + 1);
    }

    public function testCancelThreadSolved()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->setThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['solved']);

        $this->getThreadService()->cancelThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(0, $result['solved']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCancelThreadSolvedWithThreadNonExist()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->cancelThreadSolved($thread['id'] + 1);
    }

    public function testHitThread()
    {
        $thread = $this->createProtectThread();

        $this->getThreadService()->hitThread($thread['targetId'], $thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['hitNum']);

        $this->getThreadService()->hitThread($thread['targetId'], $thread['id']);
        $this->getThreadService()->hitThread($thread['targetId'], $thread['id']);
        $this->getThreadService()->hitThread($thread['targetId'], $thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(4, $result['hitNum']);
    }

    public function testSearchThreads()
    {
        $this->createTestThread1();
        $this->createTestThread2();

        $conditions = ['targetType' => 'classroom'];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, ['createdTime' => 'DESC'], 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions = ['targetId' => 1, 'type' => 'discussion'];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, ['createdTime' => 'DESC'], 0, 20);
        $this->assertEquals(1, count($foundThreads));
    }

    public function testSearchThreadCount()
    {
        $this->createProtectThread();
        $this->createTestThread1();
        $this->createTestThread2();

        $conditions = ['type' => 'question'];
        $count = $this->getThreadService()->searchThreadCount($conditions, ['createdTime' => 'DESC'], 0, 20);
        $this->assertEquals(1, $count);
    }

    public function testWaveThread()
    {
        $thread = $this->createProtectThread();
        $this->assertEquals(0, $thread['postNum']);

        $this->getThreadService()->waveThread($thread['id'], 'postNum', 2);
        $threadNew = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(2, $threadNew['postNum']);
    }

    /**
     * thread_post.
     */
    public function testGetPost()
    {
        $thread = $this->createProtectThread();

        $post = $this->createTestPost($thread);
        $foundPost = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals($post['content'], $foundPost['content']);
        $this->assertEquals($thread['id'], $post['threadId']);
    }

    public function testCreatePost()
    {
        $thread = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->assertTrue(is_array($createdPost));
        $this->assertEquals($thread['targetId'], $createdPost['targetId']);
        $this->assertEquals($thread['id'], $createdPost['threadId']);

        $thread = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $thread['postNum']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreatePostWithParentIdAndPostNonExist()
    {
        $thread = $this->createProtectThread();
        $this->createTestPost($thread, ['parentId' => 10]);
    }

    public function testCreatePostWithParentId()
    {
        $thread = $this->createProtectThread();
        $parentPost = $this->createTestPost($thread);

        $createdPost = $this->createTestPost($thread, ['parentId' => $parentPost['id']]);

        $this->assertTrue(is_array($createdPost));
        $this->assertEquals($thread['targetId'], $createdPost['targetId']);
        $this->assertEquals($thread['id'], $createdPost['threadId']);

        $thread = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(2, $thread['postNum']);
        $this->assertEquals($parentPost['id'], $createdPost['parentId']);
    }

    public function testCreatePostWithAts()
    {
        $this->createUser();
        $this->createUser(['email' => 'newUser@user.com', 'nickname' => 'newUser']);
        $thread = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread, ['content' => '@admin @user @newUser']);

        $this->assertTrue(is_array($createdPost));
        $this->assertEquals($thread['targetId'], $createdPost['targetId']);
        $this->assertEquals($thread['id'], $createdPost['threadId']);

        $thread = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $thread['postNum']);
    }

    public function testDeletePost()
    {
        $thread = $this->createProtectThread();
        $parentPost = $this->createTestPost($thread);

        $createdPost = $this->createTestPost($thread, ['parentId' => $parentPost['id']]);

        $this->getThreadService()->deletePost($createdPost['id']);

        $post = $this->getThreadService()->getPost($createdPost['id']);
        $this->assertNull($post);

        $thread = $this->getThreadService()->getThread($createdPost['threadId']);
        $this->assertEquals(1, $thread['postNum']);
    }

    public function testDeletePostsByThreadId_Wave_Correct()
    {
        $groups = $this->createGroups();

        $thread = $this->createGroupsThread($groups);

        $groupsMember = $this->createGroupsMember($groups);

        $threadContent['content'] = 'test content';

        $this->getGroupThreadService()->postThread($threadContent, $groups['id'], $groupsMember['userId'], $thread['id']);

        $this->getGroupThreadService()->deletePostsByThreadId($thread['id']);

        $postCount = $this->getThreadPostDao()->count(['threadId' => $thread['id']]);

        $groupData = $this->getGroupDao()->get($groups['id']);

        $groupMemberData = $this->getGroupMemberDao()->getByGroupIdAndUserId($groups['id'], $thread['userId']);

        $this->assertEquals($groupData['postNum'], $postCount);

        $this->assertEquals($groupMemberData['postNum'], $postCount);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testDeletePostWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->getThreadService()->deletePost($createdPost['id'] + 1);
    }

    public function testSearchPostsCount()
    {
        $thread = $this->createProtectThread();

        $post1 = $this->createTestPost($thread);
        $post2 = $this->createTestPost1($thread);
        $post3 = $this->createTestPost2($thread);

        $conditions = ['threadId' => $thread['id']];
        $count = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = ['threadId' => $thread['id'], 'greaterThanId' => $post2['id']];
        $count = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(2, $count);
    }

    public function testSearchPosts()
    {
        $thread = $this->createProtectThread();

        $post1 = $this->createTestPost($thread);
        $post2 = $this->createTestPost1($thread);
        $post3 = $this->createTestPost2($thread);

        $conditions = ['targetId' => $thread['id']];
        $foundPosts = $this->getThreadService()->searchPosts($conditions, ['createdTime' => 'DESC'], 0, 20);

        $this->assertEquals(3, count($foundPosts));
    }

    public function testSetPostAdopted()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id']);
        $foundPost = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals('1', $foundPost['adopted']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testSetPostAdoptedWithPostNonExist()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id'] + 1);
    }

    public function testCancelPostAdopted()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id']);
        $foundPost = $this->getThreadService()->getPost($post['id']);
        $this->assertEquals('1', $foundPost['adopted']);

        $this->getThreadService()->cancelPostAdopted($foundPost['id']);
        $result = $this->getThreadService()->getPost($foundPost['id']);
        $this->assertEquals('0', $result['adopted']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCancelPostAdoptedWithPostNonExist()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id']);
        $foundPost = $this->getThreadService()->getPost($post['id']);
        $this->assertEquals('1', $foundPost['adopted']);

        $this->getThreadService()->cancelPostAdopted($foundPost['id'] + 1);
    }

    public function testWavePost()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $this->assertEquals(0, $post['subposts']);

        $this->getThreadService()->wavePost($post['id'], 'subposts', 1);

        $postUpdate = $this->getThreadService()->getPost($post['id']);
        $this->assertEquals(1, $postUpdate['subposts']);
    }

    /**
     * thread_member.
     */
    public function getMember($memberId)
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread);

        $findMember = $this->getThreadService()->getMember($member['id']);

        $this->assertEquals($member['nickname'], $findMember['nickname']);
        $this->assertEquals($member['userId'], $findMember['userId']);
    }

    public function testGetMemberByThreadIdAndUserId()
    {
        $thread = $this->createProtectThread();

        $member = $this->createThreadMember1($thread['id']);
        $result = $this->getThreadService()->getMemberByThreadIdAndUserId($thread['id'], $member['userId']);

        $this->assertEquals($member['nickname'], $result['nickname']);
    }

    public function testCreateMember()
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);

        $this->assertEquals($member['nickname'], $findMember['nickname']);
        $this->assertEquals($member['userId'], $findMember['userId']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreateMemberWithMemberExist()
    {
        $thread = $this->createProtectThread();
        $this->createThreadMember1($thread['id']);
        $this->createThreadMember1($thread['id']);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreateMemberWithMemberMax()
    {
        $thread = $this->createProtectThread([
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
            'maxUsers' => 1,
        ]);
        $this->createThreadMember1($thread['id']);
        $this->createThreadMember2($thread['id']);
    }

    public function testDeleteMember()
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMember($member['id']);

        $member = $this->getThreadService()->getMember($member['id']);
        $this->assertNull($member);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testDeleteMemberWithMemberNonExist()
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMember($member['id'] + 1);
    }

    public function testDeleteMembersByThreadId()
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMembersByThreadId($thread['id']);
        $member = $this->getThreadService()->getMember($member['id']);

        $this->assertNull($member);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testDeleteMembersByThreadIdWithThreadNonExist()
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMembersByThreadId($thread['id'] + 1);
    }

    public function testSearchMembers()
    {
        $thread = $this->createProtectThread();
        $member1 = $this->createThreadMember1($thread['id']);
        $member2 = $this->createThreadMember2($thread['id']);
        $member3 = $this->createThreadMember3($thread['id']);

        $conditions = [
            'threadId' => $thread['id'],
        ];

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            20
        );
        $this->assertEquals(3, count($members));

        $conditions = [
            'threadId' => $thread['id'],
            'userId' => $member2['userId'],
        ];

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            20
        );
        $this->assertEquals(1, count($members));
    }

    public function testSearchMemberCount()
    {
        $thread = $this->createProtectThread();
        $member1 = $this->createThreadMember1($thread['id']);
        $member2 = $this->createThreadMember2($thread['id']);
        $member3 = $this->createThreadMember3($thread['id']);

        $conditions = [
            'threadId' => $thread['id'],
        ];

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = [
            'threadId' => $thread['id'],
            'userId' => $member2['id'],
        ];

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(1, $count);
    }

    public function testFilterSort()
    {
        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['popular']);
        $this->assertEquals(['hitNum' => 'DESC'], $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['created']);
        $this->assertEquals(['sticky' => 'DESC', 'createdTime' => 'DESC'], $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['posted']);
        $this->assertEquals(['sticky' => 'DESC', 'lastPostTime' => 'DESC'], $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['createdNotStick']);
        $this->assertEquals(['createdTime' => 'DESC'], $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['postedNotStick']);
        $this->assertEquals(['lastPostTime' => 'DESC'], $sort);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFilterSortWithException()
    {
        ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['']);
    }

    public function testPrepareThreadSearchConditions()
    {
        $conditions = ReflectionUtils::invokeMethod($this->getThreadService(), 'prepareThreadSearchConditions', [
            [
                'keywordType' => 'title',
                'keyword' => '1234',
                'author' => 'admin',
                'latest' => 'week',
            ],
        ]);

        $this->assertEquals('1234', $conditions['title']);
        $this->assertNotNull($conditions['userId']);
        $this->assertNotNull($conditions['GTEcreatedTime']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testPrepareThreadSearchConditionsWithException()
    {
        $conditions = ReflectionUtils::invokeMethod($this->getThreadService(), 'prepareThreadSearchConditions', [
            [
                'keywordType' => 'errorKeyWordType',
                'keyword' => '1234',
            ],
        ]);

        $this->assertEquals('1234', $conditions['title']);
        $this->assertNotNull($conditions['userId']);
        $this->assertNotNull($conditions['GTEcreatedTime']);
    }

    public function testFindThreadIds()
    {
        $this->mockBiz('Thread:ThreadDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['id' => 1], 2 => ['id' => 2], 3 => ['id' => 3]]],
        ]);

        $this->assertEquals(3, count($this->getThreadService()->findThreadIds(['userId' => 1])));
    }

    public function testFindPostThreadIds()
    {
        $this->mockBiz('Thread:ThreadPostDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['threadId' => 3], 2 => ['threadId' => 4], 3 => ['threadId' => 5]]],
        ]);

        $this->assertEquals(3, count($this->getThreadService()->findPostThreadIds(['userId' => 1])));
    }

    public function testCountPartakeThreadsByUserIdAndTargetType()
    {
        $this->mockBiz('Thread:ThreadDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['id' => 1], 2 => ['id' => 2], 3 => ['id' => 3]]],
        ]);

        $this->mockBiz('Thread:ThreadPostDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['threadId' => 3], 2 => ['threadId' => 4], 3 => ['threadId' => 5]]],
        ]);

        $this->assertEquals(5, $this->getThreadService()->countPartakeThreadsByUserIdAndTargetType(1, 'classroom'));
    }

    /**
     * thread_vote.
     */
    public function testVoteUpPost()
    {
        $thread = $this->createProtectThread();
        $post = $this->createTestPost($thread);

        $result = $this->getThreadService()->voteUpPost($post['id']);
        $this->assertEquals('ok', $result['status']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCanAccess()
    {
        $thread = $this->createProtectThread();
        $result = $this->getThreadService()->canAccess('thread.create', $thread);
        $this->assertTrue($result);

        $this->getThreadService()->canAccess('post.sticky', $thread);
    }

    public function testTryAccess()
    {
        $thread = $this->createProtectThread();
        $result = $this->getThreadService()->tryAccess('thread.create', $thread);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testTryAccessWithException()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->tryAccess('thread.unknown_event', $thread);
    }

    protected function createUser($fields = [])
    {
        $user = [];
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user123';
        $user = array_merge($user, $fields);
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = ['ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'];

        return $user;
    }

    protected function createProtectThread($fields = [], $excludeFields = [])
    {
        $textClassroom = [
            'title' => 'test',
        ];
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = [
            'title' => 'title',
            'content' => 'classroom thread content',
            'userId' => 2,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'question',
        ];

        $thread = array_merge($thread, $fields);
        foreach ($excludeFields as $excludeField) {
            if (isset($thread[$excludeField])) {
                unset($thread[$excludeField]);
            }
        }

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestThread1()
    {
        $textClassroom = [
            'title' => 'test1',
        ];
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = [
            'title' => 'title1',
            'content' => 'thread content1',
            'userId' => 1,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'discussion',
        ];

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestThread2()
    {
        $textClassroom = [
            'title' => 'test2',
        ];
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = [
            'title' => 'title2',
            'content' => 'thread content2',
            'userId' => 1,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'discussion',
        ];

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestPost($thread, $fields = [])
    {
        $post = [
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread',
        ];
        $post = array_merge($post, $fields);

        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost1($thread)
    {
        $post = [
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread1',
        ];

        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost2($thread)
    {
        $post = [
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread2',
        ];

        return $this->getThreadService()->createPost($post);
    }

    protected function createThreadMember1($threadId)
    {
        $fields = [
            'threadId' => $threadId,
            'userId' => 1,
            'nickname' => 'xiaofang',
        ];

        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember2($threadId)
    {
        $fields = [
            'threadId' => $threadId,
            'userId' => 2,
            'nickname' => 'lisan',
        ];

        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember3($threadId)
    {
        $fields = [
            'threadId' => $threadId,
            'userId' => 3,
            'nickname' => 'zhaosi',
        ];

        return $this->getThreadService()->createMember($fields);
    }

    protected function createGroups()
    {
        $groups = [
            'title' => 'test group',
            'about' => 'test group about',
            'ownerId' => 1,
        ];

        return $this->getGroupDao()->create($groups);
    }

    protected function createGroupsThread($groups)
    {
        $groupsThread = [
            'title' => 'test thread',
            'content' => 'test groupId content',
            'groupId' => $groups['id'],
            'userId' => 1,
        ];

        return $this->getGroupThreadDao()->create($groupsThread);
    }

    protected function createGroupsMember($groups)
    {
        $groupsMember = [
            'groupId' => $groups['id'],
            'userId' => 2,
        ];

        return $this->getGroupMemberDao()->create($groupsMember);
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    protected function getGroupThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getGroupDao()
    {
        return $this->createDao('Group:GroupDao');
    }

    protected function getGroupThreadDao()
    {
        return $this->createDao('Group:ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Group:ThreadPostDao');
    }

    protected function getGroupMemberDao()
    {
        return $this->createDao('Group:MemberDao');
    }
}
