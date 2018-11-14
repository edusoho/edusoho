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

        $this->assertArrayEquals($thread, $threadNew);
    }

    public function testCreateThread()
    {
        $thread = $this->createProtectThread();

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     * @expectedExceptionMessage exception.thread.title_required
     */
    public function testCreatedThreadWithNoneTitleException()
    {
        $thread = $this->createProtectThread(array(), array('title'));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreatedThreadWithNoneContentException()
    {
        $thread = $this->createProtectThread(array(), array('content'));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreatedThreadWithNoneTargetIdException()
    {
        $thread = $this->createProtectThread(array(), array('targetId'));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    /**
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testCreatedThreadWithNoneTypeException()
    {
        $thread = $this->createProtectThread(array('type' => 'unknown'));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    public function testCreateThreadWithEventType()
    {
        $thread = $this->createProtectThread(array(
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
        ));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
    }

    public function testCreateThreadWithAts()
    {
        $currentUser = $this->getCurrentUser();
        $this->createUser();
        $newUser = $this->createUser(array('email' => 'newUser@user.com', 'nickname' => 'newUser'));
        $thread = $this->createProtectThread(array(
            'content' => '@user @newUser @admin',
            'userId' => $currentUser['id'],
        ));

        $threadNew = $this->getThreadService()->getThread($thread['id']);

        $this->assertArrayEquals($thread, $threadNew);
        $this->assertContains($newUser['id'], $threadNew['ats']);
    }

    public function testUpdateThread()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread = $this->createProtectThread();

        $fields = array(
            'title' => 'title2',
            'content' => 'hello123',
            'startTime' => 'now',
        );
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

        $fields = array(
            'title' => 'title2',
            'content' => 'hello123',
            'startTime' => time(),
        );
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

        $fields = array();
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
        $thread = $this->createProtectThread(array(
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
        ));
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

        $conditions = array('targetType' => 'classroom');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, array('createdTime' => 'DESC'), 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions = array('targetId' => 1, 'type' => 'discussion');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, array('createdTime' => 'DESC'), 0, 20);
        $this->assertEquals(1, count($foundThreads));
    }

    public function testSearchThreadCount()
    {
        $this->createProtectThread();
        $this->createTestThread1();
        $this->createTestThread2();

        $conditions = array('type' => 'question');
        $count = $this->getThreadService()->searchThreadCount($conditions, array('createdTime' => 'DESC'), 0, 20);
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
        $this->createTestPost($thread, array('parentId' => 10));
    }

    public function testCreatePostWithParentId()
    {
        $thread = $this->createProtectThread();
        $parentPost = $this->createTestPost($thread);

        $createdPost = $this->createTestPost($thread, array('parentId' => $parentPost['id']));

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
        $this->createUser(array('email' => 'newUser@user.com', 'nickname' => 'newUser'));
        $thread = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread, array('content' => '@admin @user @newUser'));

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

        $createdPost = $this->createTestPost($thread, array('parentId' => $parentPost['id']));

        $this->getThreadService()->deletePost($createdPost['id']);

        $post = $this->getThreadService()->getPost($createdPost['id']);
        $this->assertNull($post);

        $thread = $this->getThreadService()->getThread($createdPost['threadId']);
        $this->assertEquals(1, $thread['postNum']);
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

        $conditions = array('threadId' => $thread['id']);
        $count = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = array('threadId' => $thread['id'], 'greaterThanId' => $post2['id']);
        $count = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(2, $count);
    }

    public function testSearchPosts()
    {
        $thread = $this->createProtectThread();

        $post1 = $this->createTestPost($thread);
        $post2 = $this->createTestPost1($thread);
        $post3 = $this->createTestPost2($thread);

        $conditions = array('targetId' => $thread['id']);
        $foundPosts = $this->getThreadService()->searchPosts($conditions, array('createdTime' => 'DESC'), 0, 20);

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
        $thread = $this->createProtectThread(array(
            'type' => 'event',
            'location' => '12345test str',
            'startTime' => 'now',
            'maxUsers' => 1,
        ));
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

        $conditions = array(
            'threadId' => $thread['id'],
        );

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            20
        );
        $this->assertEquals(3, count($members));

        $conditions = array(
            'threadId' => $thread['id'],
            'userId' => $member2['userId'],
        );

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
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

        $conditions = array(
            'threadId' => $thread['id'],
        );

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = array(
            'threadId' => $thread['id'],
            'userId' => $member2['id'],
        );

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(1, $count);
    }

    public function testFilterSort()
    {
        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('popular'));
        $this->assertEquals(array('hitNum' => 'DESC'), $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('created'));
        $this->assertEquals(array('sticky' => 'DESC', 'createdTime' => 'DESC'), $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('posted'));
        $this->assertEquals(array('sticky' => 'DESC', 'lastPostTime' => 'DESC'), $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('createdNotStick'));
        $this->assertEquals(array('createdTime' => 'DESC'), $sort);

        $sort = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('postedNotStick'));
        $this->assertEquals(array('lastPostTime' => 'DESC'), $sort);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFilterSortWithException()
    {
        ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array(''));
    }

    public function testPrepareThreadSearchConditions()
    {
        $conditions = ReflectionUtils::invokeMethod($this->getThreadService(), 'prepareThreadSearchConditions', array(
            array(
                'keywordType' => 'title',
                'keyword' => '1234',
                'author' => 'admin',
                'latest' => 'week',
            ),
        ));

        $this->assertEquals('1234', $conditions['title']);
        $this->assertNotNull($conditions['userId']);
        $this->assertNotNull($conditions['GTEcreatedTime']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testPrepareThreadSearchConditionsWithException()
    {
        $conditions = ReflectionUtils::invokeMethod($this->getThreadService(), 'prepareThreadSearchConditions', array(
            array(
                'keywordType' => 'errorKeyWordType',
                'keyword' => '1234',
            ),
        ));
        var_dump($conditions);

        $this->assertEquals('1234', $conditions['title']);
        $this->assertNotNull($conditions['userId']);
        $this->assertNotNull($conditions['GTEcreatedTime']);
    }

    public function testFindThreadIds()
    {
        $this->mockBiz('Thread:ThreadDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3))),
        ));

        $this->assertEquals(3, count($this->getThreadService()->findThreadIds(array('userId' => 1))));
    }

    public function testFindPostThreadIds()
    {
        $this->mockBiz('Thread:ThreadPostDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('threadId' => 3), 2 => array('threadId' => 4), 3 => array('threadId' => 5))),
        ));

        $this->assertEquals(3, count($this->getThreadService()->findPostThreadIds(array('userId' => 1))));
    }

    public function testCountPartakeThreadsByUserIdAndTargetType()
    {
        $this->mockBiz('Thread:ThreadDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3))),
        ));

        $this->mockBiz('Thread:ThreadPostDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('threadId' => 3), 2 => array('threadId' => 4), 3 => array('threadId' => 5))),
        ));

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
     * @expectedException \Biz\Thread\ThreadException
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
     * @expectedException \Biz\Thread\ThreadException
     */
    public function testTryAccessWithException()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->tryAccess('thread.unknown_event', $thread);
    }

    protected function createUser($fields = array())
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';
        $user = array_merge($user, $fields);
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        return $user;
    }

    protected function createProtectThread($fields = array(), $excludeFields = array())
    {
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title' => 'title',
            'content' => 'classroom thread content',
            'userId' => 2,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'question',
        );

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
        $textClassroom = array(
            'title' => 'test1',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title' => 'title1',
            'content' => 'thread content1',
            'userId' => 1,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'discussion',
        );

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestThread2()
    {
        $textClassroom = array(
            'title' => 'test2',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title' => 'title2',
            'content' => 'thread content2',
            'userId' => 1,
            'targetId' => $classroom['id'],
            'targetType' => 'classroom',
            'type' => 'discussion',
        );

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestPost($thread, $fields = array())
    {
        $post = array(
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread',
        );
        $post = array_merge($post, $fields);

        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost1($thread)
    {
        $post = array(
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread1',
        );

        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost2($thread)
    {
        $post = array(
            'targetId' => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId' => $thread['id'],
            'content' => 'post thread2',
        );

        return $this->getThreadService()->createPost($post);
    }

    protected function createThreadMember1($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId' => 1,
            'nickname' => 'xiaofang',
        );

        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember2($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId' => 2,
            'nickname' => 'lisan',
        );

        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember3($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId' => 3,
            'nickname' => 'zhaosi',
        );

        return $this->getThreadService()->createMember($fields);
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
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
}
