<?php
namespace Tests\Thread;

use Biz\User\CurrentUser;
//  use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;

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

    public function testUpdateThread()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread = $this->createProtectThread();

        $fields = array(
            'title'   => 'title2',
            'content' => 'hello123'
        );
        $threadUpdate = $this->getThreadService()->updateThread($thread['id'], $fields);

        $this->assertEquals($fields['title'], $threadUpdate['title']);
    }

    public function testDeleteThread()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $thread      = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->getThreadService()->deleteThread($Thread['id']);

        $foundThread = $this->getThreadService()->getThread($Thread['id']);
        $foundPost   = $this->getThreadService()->getPost($createPost['id']);

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

    public function testSetThreadNice()
    {
        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadNice($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $result['nice']);
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

    public function testSetThreadSolved()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $thread = $this->createProtectThread();
        $this->getThreadService()->setThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $result['solved']);
    }

    public function testCancelThreadSolved()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $thread = $this->createProtectThread();

        $this->getThreadService()->setThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['solved']);

        $this->getThreadService()->cancelThreadSolved($thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(0, $result['solved']);
    }

    public function testHitThread()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();

        $this->getThreadService()->hitThread($thread['targetId'], $thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(1, $result['hitNum']);

        $this->getThreadService()->hitThread($Thread['targetId'], $thread['id']);
        $this->getThreadService()->hitThread($Thread['targetId'], $thread['id']);
        $this->getThreadService()->hitThread($Thread['targetId'], $thread['id']);
        $result = $this->getThreadService()->getThread($thread['id']);
        $this->assertEquals(4, $result['hitNum']);
    }

    public function testSearchThreads()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $textClassroom = array(
        'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);*/

        $this->createTestThread1();
        $this->createTestThread2();

        $conditions   = array('targetId' => 1);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, array('createdTime' => 'DESC'), 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions   = array('targetId' => 1, 'type' => 'discussion');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, array('createdTime' => 'DESC'), 0, 20);
        $this->assertEquals(1, count($foundThreads));
    }

    public function testSearchThreadCount()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $textClassroom = array(
        'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);*/

        $this->createTestThread1();
        $this->createTestThread2();

        $conditions = array('type' => 'question');
        $count      = $this->getThreadService()->searchThreadCount($conditions, array('createdTime' => 'DESC'), 0, 20);
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
     * thread_post
     */

    public function testGetPost()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();

        $post      = $this->createTestPost($thread);
        $foundPost = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals($post['content'], $foundPost['content']);
        $this->assertEquals($thread['id'], $post['threadId']);
    }

    public function testCreatePost()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread      = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->assertTrue(is_array($createdPost));
        $this->assertEquals($thread['targetId'], $createdPost['targetId']);
        $this->assertEquals($thread['id'], $createdPost['threadId']);

        $thread = $this->getThreadService()->getThread($post['threadId']);
        $this->assertEquals(1, $thread['postNum']);
    }

    public function testDeletePost()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread      = $this->createProtectThread();
        $createdPost = $this->createTestPost($thread);

        $this->getThreadService()->deletePost($createdPost['id']);

        $post = $this->getThreadService()->getPost($createdPost['id']);
        $this->assertNull($post);

        $thread = $this->getThreadService()->getThread($createdPost['threadId']);
        $this->assertEquals(0, $thread['postNum']);
    }

    public function testSearchPostsCount()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();

        $post1 = $this->createTestPost($thread);
        $post2 = $this->createTestPost1($thread);
        $post3 = $this->createTestPost2($thread);

        $conditions = array('threadId' => $thread['id']);
        $count      = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = array('threadId' => $thread['id'], 'greaterThanId' => $post2['id']);
        $count      = $this->getThreadService()->searchPostsCount($conditions);
        $this->assertEquals(2, $count);
    }

    public function testSearchPosts()
    {
        /* $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();

        $post1 = $this->createTestPost($thread);
        $post2 = $this->createTestPost1($thread);
        $post3 = $this->createTestPost2($thread);

        $conditions = array('targetId' => $thread['id']);
        $foundPosts = $this->getThreadService()->searchPosts($conditions, 'created', 0, 20);

        $this->assertEquals(3, count($foundPosts));
    }

    public function testSetPostAdopted()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $thread = $this->createProtectThread();
        $post   = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id']);
        $foundPost = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals('1', $foundPost['adopted']);
    }

    public function testCancelPostAdopted()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $thread = $this->createProtectThread();
        $post   = $this->createTestPost($thread);

        $this->getThreadService()->setPostAdopted($post['id']);
        $foundPost = $this->getThreadService()->getPost($post['id']);
        $this->assertEquals('1', $foundPost['adopted']);

        $this->getThreadService()->cancelPostAdopted($foundPost['id']);
        $result = $this->getThreadService()->getPost($foundPost['id']);
        $this->assertEquals('0', $result['adopted']);
    }

    public function testWavePost()
    {
        $thread = $this->createProtectThread();
        $post   = $this->createTestPost($thread);

        $this->assertEquals(0, $result['subposts']);

        $this->wavePost($post['id'], 'subposts', 1);

        $postUpdate = $this->getThreadService()->getPost($post['id']);
        $this->assertEquals(1, $result['subposts']);
    }

    /**
     * thread_member
     */

    public function getMember($memberId)
    {
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread);

        $findMeber = $this->getThreadService()->getMember($member['id']);

        $this->assertEquals($member['nickname'], $findMember['nickname']);
        $this->assertEquals($member['userId'], $findMember['userId']);
    }

    public function testGetMemberByThreadIdAndUserId()
    {
        $thread = $this->createProtectThread();
        /*$currentUser = new CurrentUser();
        $currentUser->fromArray(array(
        'id'        => 1,
        'nickname'  => 'user',
        'email'     => 'user@user.com',
        'password'  => 'user',
        'currentIp' => '127.0.0.1',
        'roles'     => array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $member = $this->createThreadMember1($thread['id']);
        $result = $this->getThreadService()->getMemberByThreadIdAndUserId($thread['id'], $member['userId']);

        $this->assertEquals($member['nickname'], $result['nickname']);
    }

    public function testCreateMember()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/

        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMeber = $this->getThreadService()->getMember($member['id']);

        $this->assertEquals($member['nickname'], $findMember['nickname']);
        $this->assertEquals($member['userId'], $findMember['userId']);
    }

    public function testDeleteMember()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMember($member['id']);

        $member = $this->getThreadService()->getMember($member['id']);
        $this->assertNull($member);
    }

    public function testDeleteMembersByThreadId()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();
        $member = $this->createThreadMember1($thread['id']);

        $findMember = $this->getThreadService()->getMember($member['id']);
        $this->assertEquals($member['nickname'], $findMember['nickname']);

        $this->getThreadService()->deleteMembersByThreadId($thread['id']);
        $member = $this->getThreadService()->getMember($member['id']);

        $this->assertNull($member);
    }

    public function testSearchMembers()
    {
        $thread  = $this->createProtectThread();
        $member1 = $this->createThreadMember1($thread['id']);
        $member2 = $this->createThreadMember2($thread['id']);
        $member3 = $this->createThreadMember3($thread['id']);

        $conditions = array(
            'threadId' => $thread['id']
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
            'userId'   => $member2['userId']
        );

        $members = $this->getThreadService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            20
        );
        $this->assertEquals(1, count($members));
    }

    public function searchMemberCount($conditions)
    {
        $thread  = $this->createProtectThread();
        $member1 = $this->createThreadMember1($thread['id']);
        $member2 = $this->createThreadMember2($thread['id']);
        $member3 = $this->createThreadMember3($thread['id']);

        $conditions = array(
            'threadId' => $thread['id']
        );

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(3, $count);

        $conditions = array(
            'threadId' => $thread['id'],
            'userId'   => $member2['id']
        );

        $count = $this->getThreadService()->searchMemberCount($conditions);
        $this->assertEquals(1, $count);
    }

    /**
     * thread_vote
     */

    public function testVoteUpPost()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();
        $post   = $this->createTestPost($thread);

        $result = $this->getThreadService()->voteUpPost($post['id']);
        $this->assertEquals('ok', $result['status']);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testCanAccess()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();
        $result = $this->getThreadService()->canAccess('thread.create', $thread);
        $this->assertTrue($result);

        $this->getThreadService()->canAccess('post.sticky', $thread);
    }

    public function testTryAccess()
    {
        /*$user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);*/
        $thread = $this->createProtectThread();
        $this->getThreadService()->tryAccess('thread.create', $thread);
    }

    /**
     * 话题成员
     *
     */
    public function testFindMembersCountByThreadId()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 2,
            'nickname'  => 'user',
            'email'     => 'user@user.com',
            'password'  => 'user',
            'currentIp' => '127.0.0.1',
            'roles'     => array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $Thread = $this->createProtectThread();
        $fields = array(
            'threadId' => $Thread['id'],
            'userId'   => $currentUser['id'],
            'nickname' => 'xiaofang'
        );
        $member       = $this->getThreadService()->createMember($fields);
        $currentUser2 = new CurrentUser();
        $currentUser2->fromArray(array(
            'id'        => 1,
            'nickname'  => 'user2',
            'email'     => 'user2@user.com',
            'password'  => 'user',
            'currentIp' => '127.0.0.1',
            'roles'     => array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser2);
        $fields2 = array(
            'threadId' => $Thread['id'],
            'userId'   => $currentUser2['id'],
            'nickname' => 'xiaofang'
        );
        $member      = $this->getThreadService()->createMember($fields2);
        $memberCount = $this->getThreadService()->findMembersCountByThreadId($Thread['id']);
        $this->assertEquals(2, $memberCount);
    }

    public function testFindMembersByThreadId()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 1,
            'nickname'  => 'user',
            'email'     => 'user@user.com',
            'password'  => 'user',
            'currentIp' => '127.0.0.1',
            'roles'     => array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $Thread = $this->createProtectThread();
        $fields = array(
            'threadId' => $Thread['id'],
            'userId'   => $currentUser['id'],
            'nickname' => 'xiaofang'
        );
        $member     = $this->getThreadService()->createMember($fields);
        $findMember = $this->getThreadService()->findMembersByThreadId($Thread['id'], 0, 11);
        $this->assertEquals(1, count($findMember));
    }

    public function testFindMembersByThreadIdAndUserIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $Thread = $this->createProtectThread();

        $fields = array(
            'threadId' => $Thread['id'],
            'userId'   => $currentUser['id'],
            'nickname' => 'xiaofang'
        );
        $member = $this->getThreadService()->createMember($fields);
        $result = $this->getThreadService()->findMembersByThreadIdAndUserIds($Thread['id'], array($currentUser['id']));
        $this->assertEquals('1', count($result));
    }

    protected function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    protected function createProtectThread()
    {
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title'      => 'title',
            'content'    => 'classroom thread content',
            'userId'     => 2,
            'targetId'   => $classroom['id'],
            'targetType' => 'classroom',
            'type'       => 'question'
        );

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestThread1()
    {
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title'      => 'title',
            'content'    => 'xxx',
            'userId'     => 1,
            'targetId'   => $classroom['id'],
            'targetType' => 'classroom',
            'type'       => 'discussion'
        );

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestThread2()
    {
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array(
            'title'      => 'title',
            'content'    => 'xxx',
            'userId'     => 1,
            'targetId'   => $classroom['id'],
            'targetType' => 'classroom',
            'type'       => 'discussion'
        );

        return $this->getThreadService()->createThread($thread);
    }

    protected function createTestPost($thread)
    {
        $post = array(
            'targetId'   => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId'   => $thread['id'],
            'content'    => 'post thread'
        );
        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost1($thread)
    {
        $post = array(
            'targetId'   => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId'   => $thread['id'],
            'content'    => 'post thread1'
        );
        return $this->getThreadService()->createPost($post);
    }

    protected function createTestPost2($thread)
    {
        $post = array(
            'targetId'   => $thread['targetId'],
            'targetType' => $thread['targetType'],
            'threadId'   => $thread['id'],
            'content'    => 'post thread2'
        );
        return $this->getThreadService()->createPost($post);
    }

    protected function createThreadMember1($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId'   => 1,
            'nickname' => 'xiaofang'
        );
        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember2($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId'   => 2,
            'nickname' => 'lisan'
        );
        return $this->getThreadService()->createMember($fields);
    }

    protected function createThreadMember3($threadId)
    {
        $fields = array(
            'threadId' => $threadId,
            'userId'   => 3,
            'nickname' => 'zhaosi'
        );
        return $this->getThreadService()->createMember($fields);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread:ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article:ArticleService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

}
