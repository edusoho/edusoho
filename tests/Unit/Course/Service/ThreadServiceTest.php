<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\ThreadService;
use AppBundle\Common\ReflectionUtils;

class ThreadServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testGetThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $foundThread = $this->getThreadService()->getThread($thread['courseId'], $createdThread['id']);

        $this->assertTrue(is_array($foundThread));
        $this->assertEquals($thread['courseId'], $foundThread['courseId']);
    }

    public function testGetNotExistThread()
    {
        $foundThread = $this->getThreadService()->getThread(2, 3);
        $this->assertNull($foundThread);
    }

    /**
     * @group current
     */
    public function testGetThreadWithErrorCourseId()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $this->assertTrue(is_array($createdThread));

        // 新程序只检查第二个参数
        // $errorCoruseId = $thread['courseId'] + 1;
        // $foundThread = $this->getThreadService()->getThread($errorCoruseId, $createdThread['id']);
        // $this->assertNull($foundThread);
    }

    /**
     * @group current
     */
    public function testFindThreadsByType()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread);
        $this->getThreadService()->createThread($thread);

        $thread = array(
            'courseId' => 1,
            'type' => 'question',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread);
        $this->getThreadService()->createThread($thread);
        $this->getThreadService()->createThread($thread);

        $foundThreads = $this->getThreadService()->findThreadsByType($thread['courseId'], 'question', 'latestCreated', 0, 20);

        $this->assertEquals(3, count($foundThreads));

        foreach ($foundThreads as $foundThread) {
            $this->assertEquals('question', $foundThread['type']);
        }
    }

    /**
     * @group current
     */
    public function testSearchThreads()
    {
        $course1 = $this->createDemoCourse();
        $thread1 = array(
            'courseId' => $course1['id'],
            'taskId' => 0,
            'type' => 'discussion',
            'title' => 'test thread 1 ',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread1);

        $thread2 = array(
            'courseId' => $course1['id'],
            'taskId' => 1,
            'type' => 'discussion',
            'title' => 'test thread 2',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread2);

        $thread3 = array(
            'courseId' => $course1['id'],
            'taskId' => 1,
            'type' => 'question',
            'title' => 'test thread 3',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread3);

        $course2 = $this->createDemoCourse();

        $thread4 = array(
            'courseId' => $course2['id'],
            'taskId' => 0,
            'type' => 'discussion',
            'title' => 'test thread 3',
            'content' => 'test content',
        );
        $this->getThreadService()->createThread($thread4);

        $conditions = array('courseId' => $course1['id']);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(3, count($foundThreads));

        $conditions = array('courseId' => $course1['id'], 'taskId' => 1);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions = array('courseId' => $course1['id'], 'type' => 'question');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }

        $conditions = array('courseId' => $course1['id'], 'taskId' => 1, 'type' => 'question');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }
    }

    public function testSearchThreadPosts()
    {
        $course = $this->createDemoCourse();

        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $post1 = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread1',
        );
        $createdPost = $this->getThreadService()->createPost($post1);
        $post2 = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread2',
        );
        $createdPost = $this->getThreadService()->createPost($post2);
        $post3 = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread3',
        );
        $createdPost = $this->getThreadService()->createPost($post3);
        $conditions = array('courseId' => $course['id']);
        $posts = $this->getThreadService()->searchThreadPosts($conditions, 'createdTimeByDesc', 0, 10);
    }

    /**
     * @group current
     */
    public function testCreateThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $this->assertTrue(is_array($createdThread));
        $this->assertEquals($thread['courseId'], $createdThread['courseId']);
        $this->assertEquals($this->getCurrentUser()->id, $createdThread['userId']);
        $this->assertGreaterThan(0, $createdThread['createdTime']);
    }

    /**
     * @group current
     * @expectedException \Biz\Course\ThreadException
     */
    public function testCreateThreadWithEmptyCourseId()
    {
        $thread = array(
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
    }

    /**
     * @group current
     * @expectedException \Biz\Course\ThreadException
     */
    public function testCreateThreadWithEmptyType()
    {
        $thread = array(
            'courseId' => 1,
            'type' => '',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
    }

    /**
     * @group test
     */
    public function testDeleteThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);

        $this->getThreadService()->deleteThread($createdThread['id']);

        $foundThread = $this->getThreadService()->getThread($createdThread['courseId'], $createdThread['id']);
        $this->assertNull($foundThread);

        $foundPosts = $this->getThreadService()->findThreadPosts($createdThread['courseId'], $createdThread['id'], 'default', 0, 20);
        $this->assertTrue(is_array($foundPosts));
        $this->assertEmpty($foundPosts);
    }

    /**
     * @group current
     * @expectedException \Biz\Course\CourseException
     */
    public function testPostOnNotExistCourse()
    {
        $notExistThread = array(
            'id' => 999,
            'courseId' => 888,
            'content' => 'not exist content',
        );
        $post = array(
            'courseId' => $notExistThread['courseId'],
            'threadId' => $notExistThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);
    }

    /**
     * @group current
     */
    public function testFindThreadPosts()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $this->getThreadService()->createPost($post);
        $this->getThreadService()->createPost($post);
        $this->getThreadService()->createPost($post);

        $foundPosts = $this->getThreadService()->findThreadPosts($thread['courseId'], $createdThread['id'], 'default', 0, 20);
        $this->assertEquals(3, count($foundPosts));

        foreach ($foundPosts as $foundPost) {
            $this->assertEquals($post['threadId'], $foundPost['threadId']);
        }
    }

    /**
     * @group current
     */
    public function testCreatePost()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);

        $this->assertTrue(is_array($createdPost));
        $this->assertEquals($post['courseId'], $createdPost['courseId']);
        $this->assertEquals($post['threadId'], $createdPost['threadId']);

        $thread = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);
        $this->assertEquals(1, $thread['postNum']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreatePostWithError()
    {
        $this->getThreadService()->createPost(array());
    }

    public function testDeletePost()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);

        $this->getThreadService()->deletePost($createdPost['courseId'], $createdPost['id']);

        $foundPosts = $this->getThreadService()->findThreadPosts($createdPost['courseId'], $createdPost['threadId'], 'default', 0, 20);

        $this->assertTrue(is_array($foundPosts));
        $this->assertEmpty($foundPosts);

        $thread = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);
        $this->assertEquals(0, $thread['postNum']);
    }

    public function testGetMyReplyThreadCount()
    {
        $result = $this->getThreadService()->getMyReplyThreadCount();
        $this->assertEquals(0, $result);
    }

    public function testGetMyLatestReplyPerThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);

        $result = $this->getThreadService()->getMyLatestReplyPerThread(0, 10);

        $this->assertEquals($createdPost, array_shift($result));
    }

    public function testFindThreadIds()
    {
        $this->mockBiz('Course:ThreadDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3))),
        ));

        $this->assertEquals(3, count($this->getThreadService()->findThreadIds(array('userId' => 1))));
    }

    public function testFindPostThreadIds()
    {
        $this->mockBiz('Course:ThreadPostDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('threadId' => 3), 2 => array('threadId' => 4), 3 => array('threadId' => 5))),
        ));

        $this->assertEquals(3, count($this->getThreadService()->findPostThreadIds(array('userId' => 1))));
    }

    public function testCountPartakeThreadsByUserId()
    {
        $this->mockBiz('Course:ThreadDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3))),
        ));

        $this->mockBiz('Course:ThreadPostDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1 => array('threadId' => 3), 2 => array('threadId' => 4), 3 => array('threadId' => 5))),
        ));

        $this->assertEquals(5, $this->getThreadService()->countPartakeThreadsByUserId(1));
    }

    public function testCountThreads()
    {
        $result = $this->getThreadService()->countThreads(array());
        $this->assertEquals(0, $result);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testGetThreadWithError()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 1, 'title' => 'test', 'courseId' => 2),
                    'withParams' => array(1),
                ),
            )
        );
        $this->getThreadService()->getThread(1, 1);
    }

    public function testFindThreadsByTypeWithAllType()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 1, 'title' => 'test', 'courseId' => 2)),
                    'withParams' => array(array('courseId' => 1), array('latestPosted' => 'DESC'), 0, 5),
                ),
            )
        );
        $result = $this->getThreadService()->findThreadsByType(1, 'all', 'latestPosted', 0, 5);
        $this->assertEquals(array('id' => 1, 'title' => 'test', 'courseId' => 2), $result[0]);
    }

    public function testFindEliteThreadsByType()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1)),
                    'withParams' => array(array('type' => 'question', 'isElite' => 1), array('createdTime' => 'DESC'), 0, 5),
                ),
            )
        );
        $result = $this->getThreadService()->findEliteThreadsByType('question', 1, 0, 5);
        $this->assertEquals(array('id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1), $result[0]);
    }

    public function testSearchThreadCountInCourseIds()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('type' => 'question')),
                ),
            )
        );
        $result = $this->getThreadService()->searchThreadCountInCourseIds(array('type' => 'question'));
        $this->assertEquals(5, $result);
    }

    public function testSearchThreadInCourseIds()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1)),
                    'withParams' => array(array('type' => 'question'), array(), 0, 5),
                ),
            )
        );
        $result = $this->getThreadService()->searchThreadInCourseIds(array('type' => 'question'), array(), 0, 5);
        $this->assertEquals(array('id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1), $result[0]);
    }

    public function testSearchThreadPostsWithSort()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 1, 'isElite' => 1)),
                    'withParams' => array(array('isElite' => 1), array('createdTime' => 'ASC'), 0, 5),
                ),
            )
        );
        $result = $this->getThreadService()->searchThreadPosts(array('isElite' => 1), array('createdTime' => 'ASC'), 0, 5);
        $this->assertEquals(array('id' => 1, 'isElite' => 1), $result[0]);

        $result = $this->getThreadService()->searchThreadPosts(array('isElite' => 1), 'createdTimeByAsc', 0, 5);
        $this->assertEquals(array('id' => 1, 'isElite' => 1), $result[0]);
    }

    public function testSearchThreadPostsCount()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('isElite' => 1)),
                ),
            )
        );
        $result = $this->getThreadService()->searchThreadPostsCount(array('isElite' => 1));
        $this->assertEquals(5, $result);
    }

    public function testUpdateThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->updateThread(1, 1, array('content' => 'content', 'title' => 'title'));
        $this->assertEquals('title', $result['title']);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testDeleteThreadWithError()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(1),
                ),
            )
        );
        $this->getThreadService()->deleteThread(1);
    }

    public function testStickThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->stickThread(1, 1);
        $this->assertNull($result);
    }

    public function testUnstickThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->unstickThread(1, 1);
        $this->assertNull($result);
    }

    public function testEliteThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->eliteThread(1, 1);
        $this->assertNull($result);
    }

    public function testUneliteThread()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->uneliteThread(1, 1);
        $this->assertNull($result);
    }

    public function testHitThread()
    {
        $result = $this->getThreadService()->hitThread(1, 1);
        $this->assertNull($result);

        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $this->getThreadService()->hitThread(1, 1);
        $result = $this->getThreadService()->getThread(1, 1);
        $this->assertEquals(1, $result['hitNum']);
    }

    public function testGetThreadPostCount()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('threadId' => 1)),
                ),
            )
        );
        $result = $this->getThreadService()->getThreadPostCount(1, 1);
        $this->assertEquals(5, $result);
    }

    public function testFindThreadElitePosts()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('threadId' => 1)),
                    'withParams' => array(array('threadId' => 1, 'isElite' => 1), array('createdTime' => 'ASC'), 0, 5),
                ),
            )
        );
        $result = $this->getThreadService()->findThreadElitePosts(1, 1, 0, 5);
        $this->assertEquals(array('threadId' => 1), $result[0]);
    }

    public function testGetPostCountByuserIdAndThreadId()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('userId' => 1, 'threadId' => 1)),
                ),
            )
        );
        $result = $this->getThreadService()->getPostCountByuserIdAndThreadId(1, 1);
        $this->assertEquals(5, $result);
    }

    public function testGetThreadPostCountByThreadId()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => array(array('threadId' => 1)),
                ),
            )
        );
        $result = $this->getThreadService()->getThreadPostCountByThreadId(1);
        $this->assertEquals(5, $result);
    }

    public function testUpdatePost()
    {
        $course = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);
        $result = $this->getThreadService()->updatePost(1, 1, array('content' => 'content'));
        $this->assertEquals('content', $result['content']);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testUpdatePostWithError()
    {
        $this->getThreadService()->updatePost(1, 1, array('content' => 'content'));
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testDeletePostWithEmptyPost()
    {
        $course = $this->createDemoCourse();
        $this->getThreadService()->deletePost(1, 1);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testDeletePostWithErrorPost()
    {
        $course = $this->createDemoCourse();
        $course2 = $this->createDemoCourse();
        $thread = array(
            'courseId' => $course2['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        );
        $createdPost = $this->getThreadService()->createPost($post);
        $this->getThreadService()->deletePost(1, 1);
    }

    public function testPrepareThreadSearchConditions()
    {
        $service = $this->getThreadService();
        $conditions = array('threadType' => 'discussion', 'keywordType' => 'title', 'keyword' => 'test', 'author' => 'name');
        $result = ReflectionUtils::invokeMethod($service, 'prepareThreadSearchConditions', array($conditions));
        $this->assertEquals('test', $result['title']);
    }

    public function testFilterSort()
    {
        $service = $this->getThreadService();
        $result = ReflectionUtils::invokeMethod($service, 'filterSort', array('createdNotStick'));
        $this->assertEquals(array('createdTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($service, 'filterSort', array('postedNotStick'));
        $this->assertEquals(array('latestPostTime' => 'DESC'), $result);

        $result = ReflectionUtils::invokeMethod($service, 'filterSort', array('popular'));
        $this->assertEquals(array('hitNum' => 'DESC'), $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFilterSortWithErrorSort()
    {
        $service = $this->getThreadService();
        ReflectionUtils::invokeMethod($service, 'filterSort', array('error'));
    }

    protected function createDemoCourse()
    {
        $course = array(
            'title' => '教学计划Demo-'.rand(0, 1000),
            'courseSetId' => 1,
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );

        return $this->getCourseService()->createCourse($course);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}
