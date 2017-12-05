<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Service\ThreadService;

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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testPostOnNotExistThread()
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

    public function testCountPartakeThreadsByUserId()
    {
        $this->mockBiz('Course:ThreadDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(1, 2, 3)),
        ));

        $this->mockBiz('Course:ThreadPostDao', array(
            array('functionName' => 'findThreadIds', 'returnValue' => array(3, 4, 5)),
        ));

        $this->assertEquals(5, $this->getThreadService()->countPartakeThreadsByUserId(1));
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
