<?php

namespace Tests\Unit\Course\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;

class ThreadServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testGetThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
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
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
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
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $this->getThreadService()->createThread($thread);
        $this->getThreadService()->createThread($thread);

        $thread = [
            'courseId' => 1,
            'type' => 'question',
            'title' => 'test thread',
            'content' => 'test content',
        ];
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
        $thread1 = [
            'courseId' => $course1['id'],
            'taskId' => 0,
            'type' => 'discussion',
            'title' => 'test thread 1 ',
            'content' => 'test content',
        ];
        $this->getThreadService()->createThread($thread1);

        $thread2 = [
            'courseId' => $course1['id'],
            'taskId' => 1,
            'type' => 'discussion',
            'title' => 'test thread 2',
            'content' => 'test content',
        ];
        $this->getThreadService()->createThread($thread2);

        $thread3 = [
            'courseId' => $course1['id'],
            'taskId' => 1,
            'type' => 'question',
            'title' => 'test thread 3',
            'content' => 'test content',
        ];
        $this->getThreadService()->createThread($thread3);

        $course2 = $this->createDemoCourse();

        $thread4 = [
            'courseId' => $course2['id'],
            'taskId' => 0,
            'type' => 'discussion',
            'title' => 'test thread 3',
            'content' => 'test content',
        ];
        $this->getThreadService()->createThread($thread4);

        $conditions = ['courseId' => $course1['id']];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(3, count($foundThreads));

        $conditions = ['courseId' => $course1['id'], 'taskId' => 1];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions = ['courseId' => $course1['id'], 'type' => 'question'];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }

        $conditions = ['courseId' => $course1['id'], 'taskId' => 1, 'type' => 'question'];
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }
    }

    public function testSearchThreadPosts()
    {
        $course = $this->createDemoCourse();

        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $post1 = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread1',
        ];
        $createdPost = $this->getThreadService()->createPost($post1);
        $post2 = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread2',
        ];
        $createdPost = $this->getThreadService()->createPost($post2);
        $post3 = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread3',
        ];
        $createdPost = $this->getThreadService()->createPost($post3);
        $conditions = ['courseId' => $course['id']];
        $posts = $this->getThreadService()->searchThreadPosts($conditions, 'createdTimeByDesc', 0, 10);
    }

    /**
     * @group current
     */
    public function testCreateThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
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
        $thread = [
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
    }

    /**
     * @group current
     * @expectedException \Biz\Course\ThreadException
     */
    public function testCreateThreadWithEmptyType()
    {
        $thread = [
            'courseId' => 1,
            'type' => '',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
    }

    /**
     * @group test
     */
    public function testDeleteThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
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
        $notExistThread = [
            'id' => 999,
            'courseId' => 888,
            'content' => 'not exist content',
        ];
        $post = [
            'courseId' => $notExistThread['courseId'],
            'threadId' => $notExistThread['id'],
            'content' => 'post thread',
        ];
        $createdPost = $this->getThreadService()->createPost($post);
    }

    /**
     * @group current
     */
    public function testFindThreadPosts()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
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
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
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
        $this->getThreadService()->createPost([]);
    }

    public function testDeletePost()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
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
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
        $createdPost = $this->getThreadService()->createPost($post);

        $result = $this->getThreadService()->getMyLatestReplyPerThread(0, 10);

        $this->assertEquals($createdPost, array_shift($result));
    }

    public function testFindThreadIds()
    {
        $this->mockBiz('Course:ThreadDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['id' => 1], 2 => ['id' => 2], 3 => ['id' => 3]]],
        ]);

        $this->assertEquals(3, count($this->getThreadService()->findThreadIds(['userId' => 1])));
    }

    public function testFindPostThreadIds()
    {
        $this->mockBiz('Course:ThreadPostDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['threadId' => 3], 2 => ['threadId' => 4], 3 => ['threadId' => 5]]],
        ]);

        $this->assertEquals(3, count($this->getThreadService()->findPostThreadIds(['userId' => 1])));
    }

    public function testCountPartakeThreadsByUserId()
    {
        $this->mockBiz('Course:ThreadDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['id' => 1], 2 => ['id' => 2], 3 => ['id' => 3]]],
        ]);

        $this->mockBiz('Course:ThreadPostDao', [
            ['functionName' => 'findThreadIds', 'returnValue' => [1 => ['threadId' => 3], 2 => ['threadId' => 4], 3 => ['threadId' => 5]]],
        ]);

        $this->assertEquals(5, $this->getThreadService()->countPartakeThreadsByUserId(1));
    }

    public function testCountThreads()
    {
        $result = $this->getThreadService()->countThreads([]);
        $this->assertEquals(0, $result);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testGetThreadWithError()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['id' => 1, 'title' => 'test', 'courseId' => 2],
                    'withParams' => [1],
                ],
            ]
        );
        $this->getThreadService()->getThread(1, 1);
    }

    public function testFindThreadsByTypeWithAllType()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 1, 'title' => 'test', 'courseId' => 2]],
                    'withParams' => [['courseId' => 1], ['latestPosted' => 'DESC'], 0, 5],
                ],
            ]
        );
        $result = $this->getThreadService()->findThreadsByType(1, 'all', 'latestPosted', 0, 5);
        $this->assertEquals(['id' => 1, 'title' => 'test', 'courseId' => 2], $result[0]);
    }

    public function testFindEliteThreadsByType()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1]],
                    'withParams' => [['type' => 'question', 'isElite' => 1], ['createdTime' => 'DESC'], 0, 5],
                ],
            ]
        );
        $result = $this->getThreadService()->findEliteThreadsByType('question', 1, 0, 5);
        $this->assertEquals(['id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1], $result[0]);
    }

    public function testSearchThreadCountInCourseIds()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => [['type' => 'question']],
                ],
            ]
        );
        $result = $this->getThreadService()->searchThreadCountInCourseIds(['type' => 'question']);
        $this->assertEquals(5, $result);
    }

    public function testSearchThreadInCourseIds()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1]],
                    'withParams' => [['type' => 'question'], [], 0, 5],
                ],
            ]
        );
        $result = $this->getThreadService()->searchThreadInCourseIds(['type' => 'question'], [], 0, 5);
        $this->assertEquals(['id' => 1, 'title' => 'test', 'type' => 'question', 'isElite' => 1], $result[0]);
    }

    public function testSearchThreadPostsWithSort()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 1, 'isElite' => 1]],
                    'withParams' => [['isElite' => 1], ['createdTime' => 'ASC'], 0, 5],
                ],
            ]
        );
        $result = $this->getThreadService()->searchThreadPosts(['isElite' => 1], ['createdTime' => 'ASC'], 0, 5);
        $this->assertEquals(['id' => 1, 'isElite' => 1], $result[0]);

        $result = $this->getThreadService()->searchThreadPosts(['isElite' => 1], 'createdTimeByAsc', 0, 5);
        $this->assertEquals(['id' => 1, 'isElite' => 1], $result[0]);
    }

    public function testSearchThreadPostsCount()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => [['isElite' => 1]],
                ],
            ]
        );
        $result = $this->getThreadService()->searchThreadPostsCount(['isElite' => 1]);
        $this->assertEquals(5, $result);
    }

    public function testUpdateThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->updateThread($course['id'], $createdThread['id'], ['content' => 'content', 'title' => 'title']);
        self::assertEquals('title', $result['title']);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testDeleteThreadWithError()
    {
        $this->mockBiz(
            'Course:ThreadDao',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [],
                    'withParams' => [1],
                ],
            ]
        );
        $this->getThreadService()->deleteThread(1);
    }

    public function testStickThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->stickThread($course['id'], $createdThread['id']);
        self::assertNull($result);
    }

    public function testUnstickThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->unstickThread($course['id'], $createdThread['id']);
        self::assertNull($result);
    }

    public function testEliteThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->eliteThread($course['id'], $createdThread['id']);
        self::assertNull($result);
    }

    public function testUneliteThread()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $result = $this->getThreadService()->uneliteThread($course['id'], $createdThread['id']);
        self::assertNull($result);
    }

    public function testHitThread()
    {
        $result = $this->getThreadService()->hitThread(1, 1);
        self::assertNull($result);

        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);
        $this->getThreadService()->hitThread($course['id'], $createdThread['id']);
        $result = $this->getThreadService()->getThread($course['id'], $createdThread['id']);
        self::assertEquals(1, $result['hitNum']);
    }

    public function testGetThreadPostCount()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => [['threadId' => 1]],
                ],
            ]
        );
        $result = $this->getThreadService()->getThreadPostCount(1, 1);
        $this->assertEquals(5, $result);
    }

    public function testFindThreadElitePosts()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['threadId' => 1]],
                    'withParams' => [['threadId' => 1, 'isElite' => 1], ['createdTime' => 'ASC'], 0, 5],
                ],
            ]
        );
        $result = $this->getThreadService()->findThreadElitePosts(1, 1, 0, 5);
        $this->assertEquals(['threadId' => 1], $result[0]);
    }

    public function testGetPostCountByuserIdAndThreadId()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => [['userId' => 1, 'threadId' => 1]],
                ],
            ]
        );
        $result = $this->getThreadService()->getPostCountByuserIdAndThreadId(1, 1);
        $this->assertEquals(5, $result);
    }

    public function testGetThreadPostCountByThreadId()
    {
        $this->mockBiz(
            'Course:ThreadPostDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 5,
                    'withParams' => [['threadId' => 1]],
                ],
            ]
        );
        $result = $this->getThreadService()->getThreadPostCountByThreadId(1);
        $this->assertEquals(5, $result);
    }

    public function testUpdatePost()
    {
        $course = $this->createDemoCourse();
        $thread = [
            'courseId' => $course['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
        $createdPost = $this->getThreadService()->createPost($post);
        $result = $this->getThreadService()->updatePost(1, 1, ['content' => 'content']);
        $this->assertEquals('content', $result['content']);
    }

    /**
     * @expectedException \Biz\Course\ThreadException
     */
    public function testUpdatePostWithError()
    {
        $this->getThreadService()->updatePost(1, 1, ['content' => 'content']);
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
        $thread = [
            'courseId' => $course2['id'],
            'type' => 'discussion',
            'title' => 'test thread',
            'content' => 'test content',
        ];
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = [
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content' => 'post thread',
        ];
        $createdPost = $this->getThreadService()->createPost($post);
        $this->getThreadService()->deletePost(1, 1);
    }

    public function testPrepareThreadSearchConditions()
    {
        $service = $this->getThreadService();
        $conditions = ['threadType' => 'discussion', 'keywordType' => 'title', 'keyword' => 'test', 'author' => 'name'];
        $result = ReflectionUtils::invokeMethod($service, 'prepareThreadSearchConditions', [$conditions]);
        $this->assertEquals('test', $result['title']);
    }

    public function testFilterSort()
    {
        $service = $this->getThreadService();
        $result = ReflectionUtils::invokeMethod($service, 'filterSort', ['createdNotStick']);
        $this->assertEquals(['createdTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($service, 'filterSort', ['postedNotStick']);
        $this->assertEquals(['latestPostTime' => 'DESC'], $result);

        $result = ReflectionUtils::invokeMethod($service, 'filterSort', ['popular']);
        $this->assertEquals(['hitNum' => 'DESC'], $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFilterSortWithErrorSort()
    {
        $service = $this->getThreadService();
        ReflectionUtils::invokeMethod($service, 'filterSort', ['error']);
    }

    protected function createDemoCourse()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(['title' => 'nre CourseSet', 'type' => 'normal']);
        $course = [
            'title' => '教学计划Demo-'.rand(0, 1000),
            'courseSetId' => $courseSet['id'],
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ];

        return $this->getCourseService()->createCourse($course);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}
