<?php

namespace Tests\Unit\Group;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class ThreadServiceTest extends BaseTestCase
{
    public function testHideThings()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'hideThings', array(
            '[hide=coin10]lalalall[/hide]', 1, )
        );
        $result = $this->getThreadGoodsDao()->search(array(), array(), 0, 1);
        $result = $result[0];
        unset($result['createdTime']);

        $this->assertArrayEquals(array(
            'id' => 1,
            'title' => 'lalalall',
            'description' => null,
            'userId' => 1,
            'type' => 'content',
            'threadId' => '1',
            'postId' => 0,
            'coin' => 10,
            'fileId' => 0,
            'hitNum' => 0,
        ), $result);
    }

    public function testUnThreadCollect()
    {
        $testThread = array(
            'id' => 41,
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => 1,
            'userId' => 1,
        );

        $thread = $this->getThreadService()->addThread($testThread);
        $this->getThreadCollectDao()->create(array('userId' => 1, 'threadId' => $testThread['id']));
        $this->getThreadService()->unThreadCollect(1, $testThread['id']);
        $result = $this->getThreadCollectDao()->search(array(), array(), 0, 2);

        $this->assertTrue(empty($result));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnThreadCollectError2()
    {
        $testThread = array(
            'id' => 41,
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => 1,
            'userId' => 1,
        );

        $thread = $this->getThreadService()->addThread($testThread);
        $this->getThreadService()->unThreadCollect(1, $testThread['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnThreadCollectError1()
    {
        $this->getThreadService()->unThreadCollect(1, 1);
    }

    public function testAddPostAttach()
    {
        $file = $this->getFileDao()->create(array('uri' => 'test', 'mime' => 'test', 'userId' => 1));
        $result = $this->getThreadService()->addPostAttach(
            array(
                'id' => array(1, 3, 4),
                'title' => array('title1', 'title2', 'title3'),
                'description' => array('description1', 'description2', 'description3'),
                'coin' => array(1, 2, 3),
            ),
            1,
            1
        );

        $result = $this->getThreadService()->searchGoods(array(), array(), 0, 2);
        $result = $result[0];
        unset($result['createdTime']);

        $this->assertArrayEquals(array(
            'id' => 1,
            'title' => 'title1.title1',
            'description' => 'description1',
            'userId' => 1,
            'type' => 'postAttachment',
            'threadId' => 1,
            'postId' => 1,
            'coin' => 1,
            'fileId' => $file['id'],
            'hitNum' => 0,
        ), $result);
    }

    public function deleteGoods()
    {
        $result = $this->getThreadGoodsDao()->create(array('title' => 'title1', 'type' => 'content', 'threadId' => 1, 'coin' => 1));
        $this->getThreadService()->deleteGoods($result['id']);
        $result = $this->getThreadService()->get($result['id']);
        $this->assertTrue(empty($result));
    }

    public function testSearchGoods()
    {
        $this->getThreadGoodsDao()->create(array('title' => 'title1', 'type' => 'content', 'threadId' => 1, 'coin' => 1));
        $result = $this->getThreadService()->searchGoods(array(), array(), 0, 2);
        $this->assertTrue(!empty($result));
    }

    public function testAddAttach()
    {
        $this->getFileDao()->create(array('uri' => 'test', 'mime' => 'test', 'userId' => 1));
        $result = $this->getThreadService()->addAttach(
            array(
                'id' => array(1, 3, 4),
                'title' => array('title1', 'title2', 'title3'),
                'description' => array('description1', 'description2', 'description3'),
                'coin' => array(1, 2, 3),
            ),
            1,
            1
        );
        $result = $this->getThreadGoodsDao()->search(array(), array(), 0, \PHP_INT_MAX);

        $result = $result[0];
        unset($result['createdTime']);
        $this->assertArrayEquals(array(
            'id' => 1,
            'title' => 'title1.title1',
            'description' => 'description1',
            'userId' => 1,
            'type' => 'attachment',
            'threadId' => 1,
            'postId' => 0,
            'coin' => 1,
            'fileId' => 1,
            'hitNum' => 0,
        ), $result);
    }

    public function testPureString()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'pureString', array(array('course.task.activity<script>32asdf<script><br/>&nbsp;')));
        $this->assertEquals('course.task.activity32asdf', $result);
    }

    public function testSubTxt()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'subTxt', array('course.task.activity<script>32asdf<script><br/>&nbsp;.qweoiaf'));
        $this->assertEquals('course.qweoiaf', $result);
    }

    public function testFilterSort()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('byPostNum'));
        $this->assertArrayEquals(array('isStick' => 'DESC', 'postNum' => 'DESC', 'createdTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('byStick'));
        $this->assertArrayEquals(array('isStick' => 'DESC', 'createdTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('byCreatedTime'));
        $this->assertArrayEquals(array('isStick' => 'DESC', 'createdTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('byLastPostTime'));
        $this->assertArrayEquals(array('isStick' => 'DESC', 'lastPostTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('byCreatedTimeOnly'));
        $this->assertArrayEquals(array('createdTime' => 'DESC'), $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testFilterSortError()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', array('test'));
    }

    public function testThreadCollect()
    {
        $testThread = array(
            'id' => 41,
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => 1,
            'userId' => 1,
        );

        $thread = $this->getThreadService()->addThread($testThread);
        $result = $this->getThreadService()->threadCollect(2, $thread['id']);

        $this->assertTrue(!empty($result));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testThreadCollectError3()
    {
        $post = $this->getThreadCollectDao()->create(array('threadId' => 41, 'userId' => 2));
        $testThread = array(
            'id' => 41,
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => 1,
            'userId' => 1,
        );

        $thread = $this->getThreadService()->addThread($testThread);
        $result = $this->getThreadService()->threadCollect(2, $thread['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testThreadCollectError2()
    {
        $result = $this->getThreadService()->threadCollect(1, 4);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testThreadCollectError1()
    {
        $testThread = array(
            'id' => 41,
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => 1,
            'userId' => 1,
        );

        $thread = $this->getThreadService()->addThread($testThread);
        $result = $this->getThreadService()->threadCollect(1, 41);
    }

    public function testIsCollected()
    {
        $post = $this->getThreadCollectDao()->create(array('threadId' => 41, 'userId' => 1));
        $result = $this->getThreadService()->isCollected(1, 41);
        $this->assertTrue($result);

        $result = $this->getThreadService()->isCollected(2, 41);
        $this->assertNotTrue($result);
    }

    public function testCountPostsThreadIds()
    {
        $this->getThreadPostDao()->create(array('threadId' => 41, 'userId' => 1, 'content' => 1));
        $this->getThreadPostDao()->create(array('threadId' => 4, 'userId' => 1, 'content' => 12));
        $result = $this->getThreadService()->searchPostsThreadIds(array(), array(), 0, 3);
        $this->assertEquals(2, count($result));
    }

    public function testSearchPostsThreadIds()
    {
        $this->getThreadPostDao()->create(array('threadId' => 41, 'userId' => 1, 'content' => 1));
        $this->getThreadPostDao()->create(array('threadId' => 4, 'userId' => 1, 'content' => 12));
        $result = $this->getThreadService()->countPostsThreadIds(array());
        $this->assertEquals(2, $result);
    }

    public function testCountThreadCollects()
    {
        $thread = $this->getThreadCollectDao()->create(array('threadId' => 41, 'userId' => 1));
        $thread1 = $this->getThreadCollectDao()->create(array('threadId' => 4, 'userId' => 1));
        $result = $this->getThreadService()->countThreadCollects(array());
        $this->assertEquals(2, $result);

        $result = $this->getThreadService()->countThreadCollects(array('threadId' => 4));
        $this->assertEquals(1, $result);
    }

    public function testSearchThreadCollects()
    {
        $thread = $this->getThreadCollectDao()->create(array('threadId' => 41, 'userId' => 1));
        $thread1 = $this->getThreadCollectDao()->create(array('threadId' => 4, 'userId' => 1));
        $result = $this->getThreadService()->searchThreadCollects(array(), array(), 0, 1);

        $this->assertEquals(1, count($result));
        $result = $this->getThreadService()->searchThreadCollects(array(), array(), 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getThreadService()->searchThreadCollects(array('threadId' => 4), array(), 0, 2);
        $this->assertEquals(1, count($result));
    }

    public function testCountThreads()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = array(
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $count = $this->getThreadService()->countThreads(array('title' => 'test'));
        $this->assertEquals(2, $count);
    }

    public function testAddThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->assertEquals($testThread['title'], $thread['title']);
        $this->assertEquals($testThread['content'], $thread['content']);
        $this->assertEquals($testThread['groupId'], $thread['groupId']);
        $this->assertEquals($testThread['userId'], $thread['userId']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddThreadWithEmptyTitle()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => '',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddThreadWithEmptyContent()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'xxx',
            'content' => '',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddThreadWithEmptyGroupId()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'xxx',
            'content' => 'xxx',
            'groupId' => '',
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddThreadWithEmptyUserId()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => '',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => '', );

        $thread = $this->getThreadService()->addThread($testThread);
    }

    public function testGetThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $thread1 = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread, $thread1);
    }

    public function testSearchThreads()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = array(
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $threads = $this->getThreadService()->searchThreads(array('title' => 'test1'), array('isStick' => 'DESC'), 0, 10);
        $this->assertCount(1, $threads);
        $this->assertEquals($thread1, $threads[0]);
    }

    public function testGetThreadsByIds()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = array(
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $threads = $this->getThreadService()->getThreadsByIds(array($thread['id'], $thread1['id']));

        $this->assertEquals($thread['title'], $threads[$thread['id']]['title']);
        $this->assertEquals($thread1['title'], $threads[$thread1['id']]['title']);
        $this->assertEquals($thread['groupId'], $threads[$thread['id']]['groupId']);
        $this->assertEquals($thread1['groupId'], $threads[$thread1['id']]['groupId']);
    }

    public function testCloseThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('close', $thread['status']);
    }

    public function testOpenThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $this->getThreadService()->openThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('open', $thread['status']);
    }

    public function testPostThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content' => 'xxaaaaa'), $group['id'], $user['id'], $thread['id']);

        $this->assertEquals('xxaaaaa', $post['content']);
    }

    public function testGetPost()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content' => 'xxaaaaa'), $group['id'], $user['id'], $thread['id']);

        $post1 = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals($post, $post1);
    }

    /**
     * @group current
     */
    public function testSearchPosts()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content' => 'aaaaaa'), $group['id'], $user['id'], $thread['id']);
        $post1 = $this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content' => 'test1'), $group['id'], $user['id'], $thread['id']);

        $posts = $this->getThreadService()->searchPosts(array('userId' => $user['id']), array('createdTime' => 'DESC'), 0, 10);
        $this->assertCount(2, $posts);
    }

    public function testDeletePost()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content' => 'aaaaaa'), $group['id'], $user['id'], $thread['id']);

        $this->getThreadService()->deletePost($post['id']);

        $post = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals(null, $post);
    }

    public function testDeleteThread()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->deleteThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(null, $thread);
    }

    public function testSetElite()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $thread['isElite']);
    }

    public function testRemoveElite()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $this->getThreadService()->removeElite($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(0, $thread['isElite']);
    }

    public function testSetStick()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setStick($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $thread['isStick']);
    }

    /**
     * @group current
     */
    public function testRemoveStick()
    {
        $user = $this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        );
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = array(
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], );

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setStick($thread['id']);
        $this->getThreadService()->removeStick($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(0, $thread['isStick']);
    }

    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';

        return $this->getUserService()->register($user);
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Group:ThreadPostDao');
    }

    protected function createUser1()
    {
        $user = array();
        $user['email'] = 'user1@user1.com';
        $user['nickname'] = 'user1';
        $user['password'] = 'user1';

        return $this->getUserService()->register($user);
    }

    protected function getThreadCollectDao()
    {
        return $this->createDao('Group:ThreadCollectDao');
    }

    protected function getThreadGoodsDao()
    {
        return $this->createDao('Group:ThreadGoodsDao');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }

    /**
     * @return FileDao
     */
    protected function getFileDao()
    {
        return $this->createDao('Content:FileDao');
    }
}
