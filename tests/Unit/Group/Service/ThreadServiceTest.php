<?php

namespace Tests\Unit\Group\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class ThreadServiceTest extends BaseTestCase
{
    public function testHideThings()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'hideThings', [
            '[hide=coin10]lalalall[/hide]', 1, ]
        );
        $result = $this->getThreadGoodsDao()->search([], [], 0, 1);
        $result = $result[0];
        unset($result['createdTime']);

        $this->assertArrayEquals([
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
        ], $result);
    }

    public function testAddPostAttach()
    {
        $file = $this->getFileDao()->create(['uri' => 'test', 'mime' => 'test', 'userId' => 1]);
        $result = $this->getThreadService()->addPostAttach(
            [
                'id' => [1, 3, 4],
                'title' => ['title1', 'title2', 'title3'],
                'description' => ['description1', 'description2', 'description3'],
                'coin' => [1, 2, 3],
            ],
            1,
            1
        );

        $result = $this->getThreadService()->searchGoods([], [], 0, 2);
        $result = $result[0];
        unset($result['createdTime']);

        $this->assertArrayEquals([
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
        ], $result);
    }

    public function deleteGoods()
    {
        $result = $this->getThreadGoodsDao()->create(['title' => 'title1', 'type' => 'content', 'threadId' => 1, 'coin' => 1]);
        $this->getThreadService()->deleteGoods($result['id']);
        $result = $this->getThreadService()->get($result['id']);
        $this->assertTrue(empty($result));
    }

    public function testSearchGoods()
    {
        $this->getThreadGoodsDao()->create(['title' => 'title1', 'type' => 'content', 'threadId' => 1, 'coin' => 1]);
        $result = $this->getThreadService()->searchGoods([], [], 0, 2);
        $this->assertTrue(!empty($result));
    }

    public function testAddAttach()
    {
        $this->getFileDao()->create(['uri' => 'test', 'mime' => 'test', 'userId' => 1]);
        $result = $this->getThreadService()->addAttach(
            [
                'id' => [1, 3, 4],
                'title' => ['title1', 'title2', 'title3'],
                'description' => ['description1', 'description2', 'description3'],
                'coin' => [1, 2, 3],
            ],
            1,
            1
        );
        $result = $this->getThreadGoodsDao()->search([], [], 0, \PHP_INT_MAX);

        $result = $result[0];
        unset($result['createdTime']);
        $this->assertArrayEquals([
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
        ], $result);
    }

    public function testPureString()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'pureString', [['course.task.activity<script>32asdf<script><br/>&nbsp;']]);
        $this->assertEquals('course.task.activity32asdf', $result);
    }

    public function testSubTxt()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'subTxt', ['course.task.activity<script>32asdf<script><br/>&nbsp;.qweoiaf']);
        $this->assertEquals('course.qweoiaf', $result);
    }

    public function testFilterSort()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['byPostNum']);
        $this->assertArrayEquals(['isStick' => 'DESC', 'postNum' => 'DESC', 'createdTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['byStick']);
        $this->assertArrayEquals(['isStick' => 'DESC', 'createdTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['byCreatedTime']);
        $this->assertArrayEquals(['isStick' => 'DESC', 'createdTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['byLastPostTime']);
        $this->assertArrayEquals(['isStick' => 'DESC', 'lastPostTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['byCreatedTimeOnly']);
        $this->assertArrayEquals(['createdTime' => 'DESC'], $result);
    }

    /**
     * @expectedException \Biz\Group\ThreadException
     */
    public function testFilterSortError()
    {
        $result = ReflectionUtils::invokeMethod($this->getThreadService(), 'filterSort', ['test']);
    }

    public function testCountPostsThreadIds()
    {
        $this->getThreadPostDao()->create(['threadId' => 41, 'userId' => 1, 'content' => 1]);
        $this->getThreadPostDao()->create(['threadId' => 4, 'userId' => 1, 'content' => 12]);
        $result = $this->getThreadService()->searchPostsThreadIds([], [], 0, 3);
        $this->assertEquals(2, count($result));
    }

    public function testSearchPostsThreadIds()
    {
        $this->getThreadPostDao()->create(['threadId' => 41, 'userId' => 1, 'content' => 1]);
        $this->getThreadPostDao()->create(['threadId' => 4, 'userId' => 1, 'content' => 12]);
        $result = $this->getThreadService()->countPostsThreadIds([]);
        $this->assertEquals(2, $result);
    }

    public function testCountThreads()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = [
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $count = $this->getThreadService()->countThreads(['title' => 'test']);
        $this->assertEquals(2, $count);
    }

    public function testAddThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->assertEquals($testThread['title'], $thread['title']);
        $this->assertEquals($testThread['content'], $thread['content']);
        $this->assertEquals($testThread['groupId'], $thread['groupId']);
        $this->assertEquals($testThread['userId'], $thread['userId']);
    }

    /**
     * @expectedException \Biz\Group\ThreadException
     */
    public function testAddThreadWithEmptyTitle()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => '',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Biz\Group\ThreadException
     */
    public function testAddThreadWithEmptyContent()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'xxx',
            'content' => '',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Biz\Group\ThreadException
     */
    public function testAddThreadWithEmptyGroupId()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'xxx',
            'content' => 'xxx',
            'groupId' => '',
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
    }

    /**
     * @expectedException \Biz\Group\ThreadException
     */
    public function testAddThreadWithEmptyUserId()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => '',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => '', ];

        $thread = $this->getThreadService()->addThread($testThread);
    }

    public function testGetThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $thread1 = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread, $thread1);
    }

    public function testSearchThreads()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = [
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $threads = $this->getThreadService()->searchThreads(['title' => 'test1'], ['isStick' => 'DESC'], 0, 10);
        $this->assertCount(1, $threads);
        $this->assertEquals($thread1, $threads[0]);
    }

    public function testGetThreadsByIds()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);
        $testThread1 = [
            'title' => 'test1',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread1 = $this->getThreadService()->addThread($testThread1);

        $threads = $this->getThreadService()->getThreadsByIds([$thread['id'], $thread1['id']]);

        $this->assertEquals($thread['title'], $threads[$thread['id']]['title']);
        $this->assertEquals($thread1['title'], $threads[$thread1['id']]['title']);
        $this->assertEquals($thread['groupId'], $threads[$thread['id']]['groupId']);
        $this->assertEquals($thread1['groupId'], $threads[$thread1['id']]['groupId']);
    }

    public function testCloseThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('close', $thread['status']);
    }

    public function testOpenThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'test',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $this->getThreadService()->openThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('open', $thread['status']);
    }

    public function testPostThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(['fromUserId' => $user['id'], 'content' => 'xxaaaaa'], $group['id'], $user['id'], $thread['id']);

        $this->assertEquals('xxaaaaa', $post['content']);
    }

    public function testGetPost()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(['fromUserId' => $user['id'], 'content' => 'xxaaaaa'], $group['id'], $user['id'], $thread['id']);

        $post1 = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals($post, $post1);
    }

    /**
     * @group current
     */
    public function testSearchPosts()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(['fromUserId' => $user['id'], 'content' => 'aaaaaa'], $group['id'], $user['id'], $thread['id']);
        $post1 = $this->getThreadService()->postThread(['fromUserId' => $user['id'], 'content' => 'test1'], $group['id'], $user['id'], $thread['id']);

        $posts = $this->getThreadService()->searchPosts(['userId' => $user['id']], ['createdTime' => 'DESC'], 0, 10);
        $this->assertCount(2, $posts);
    }

    public function testDeletePost()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $post = $this->getThreadService()->postThread(['fromUserId' => $user['id'], 'content' => 'aaaaaa'], $group['id'], $user['id'], $thread['id']);

        $this->getThreadService()->deletePost($post['id']);

        $post = $this->getThreadService()->getPost($post['id']);

        $this->assertEquals(null, $post);
    }

    public function testDeleteThread()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->deleteThread($thread['id']);

        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(null, $thread);
    }

    public function testSetElite()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1, $thread['isElite']);
    }

    public function testRemoveElite()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

        $thread = $this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $this->getThreadService()->removeElite($thread['id']);
        $thread = $this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(0, $thread['isElite']);
    }

    public function testSetStick()
    {
        $user = $this->createUser();
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

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
        $textGroup = [
            'title' => 'textgroup',
            'about' => 'aaaaaa',
        ];
        $group = $this->getGroupService()->addGroup($user, $textGroup);
        $testThread = [
            'title' => 'test',
            'content' => 'xxx',
            'groupId' => $group['id'],
            'userId' => $user['id'], ];

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
        $user = [];
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user123';

        return $this->getUserService()->register($user);
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Group:ThreadPostDao');
    }

    protected function createUser1()
    {
        $user = [];
        $user['email'] = 'user1@user1.com';
        $user['nickname'] = 'user1';
        $user['password'] = 'user1123';

        return $this->getUserService()->register($user);
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
