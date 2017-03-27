<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class ThreadServiceTest extends BaseTestCase
{
    /**
     * @group current
     */
    public function testGetThread()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
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
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
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
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $this->getThreadService()->createThread($thread);
        $this->getThreadService()->createThread($thread);

        $thread = array(
            'courseId' => 1,
            'type'     => 'question',
            'title'    => 'test thread',
            'content'  => 'test content'
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
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));
        $thread1 = array(
            'courseId' => $course1['id'],
            'lessonId' => 0,
            'type'     => 'discussion',
            'title'    => 'test thread 1 ',
            'content'  => 'test content'
        );
        $this->getThreadService()->createThread($thread1);

        $thread2 = array(
            'courseId' => $course1['id'],
            'lessonId' => 1,
            'type'     => 'discussion',
            'title'    => 'test thread 2',
            'content'  => 'test content'
        );
        $this->getThreadService()->createThread($thread2);

        $thread3 = array(
            'courseId' => $course1['id'],
            'lessonId' => 1,
            'type'     => 'question',
            'title'    => 'test thread 3',
            'content'  => 'test content'
        );
        $this->getThreadService()->createThread($thread3);

        $course2 = $this->getCourseService()->createCourse(array('title' => 'test course 2'));

        $thread4 = array(
            'courseId' => $course2['id'],
            'lessonId' => 0,
            'type'     => 'discussion',
            'title'    => 'test thread 3',
            'content'  => 'test content'
        );
        $this->getThreadService()->createThread($thread4);

        $conditions   = array('courseId' => $course1['id']);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(3, count($foundThreads));

        $conditions   = array('courseId' => $course1['id'], 'lessonId' => 1);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($foundThreads));

        $conditions   = array('courseId' => $course1['id'], 'type' => 'question');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }

        $conditions   = array('courseId' => $course1['id'], 'lessonId' => 1, 'type' => 'question');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));

        foreach ($foundThreads as $thread) {
            $this->assertEquals('question', $thread['type']);
        }
    }

    public function testSearchThreadPosts()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));

        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);
        $post1         = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread1'
        );
        $createdPost = $this->getThreadService()->createPost($post1);
        $post2       = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread2'
        );
        $createdPost = $this->getThreadService()->createPost($post2);
        $post3       = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread3'
        );
        $createdPost = $this->getThreadService()->createPost($post3);
        $conditions  = array('courseId' => $course['id']);
        $posts       = $this->getThreadService()->searchThreadPosts($conditions, "createdTimeByDesc", 0, 10);
    }

    /**
     * @group current
     */
    public function testCreateThread()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $this->assertTrue(is_array($createdThread));
        $this->assertEquals($thread['courseId'], $createdThread['courseId']);
        $this->assertEquals($this->getCurrentUser()->id, $createdThread['userId']);
        $this->assertGreaterThan(0, $createdThread['createdTime']);
    }

    /**
     * @group current
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateThreadWithEmptyCourseId()
    {
        $thread = array(
            'type'    => 'discussion',
            'title'   => 'test thread',
            'content' => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);
    }

    /**
     * @group test
     */
    public function testDeleteThread()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread'
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
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testPostOnNotExistThread()
    {
        $notExistThread = array(
            'id'       => 999,
            'courseId' => 888,
            'content'  => 'not exist content'
        );
        $post = array(
            'courseId' => $notExistThread['courseId'],
            'threadId' => $notExistThread['id'],
            'content'  => 'post thread'
        );
        $createdPost = $this->getThreadService()->createPost($post);
    }

    /**
     * @group current
     */
    public function testFindThreadPosts()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread'
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
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread'
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
        $course = $this->getCourseService()->createCourse(array('title' => 'test course'));
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        $createdThread = $this->getThreadService()->createThread($thread);

        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread'
        );
        $createdPost = $this->getThreadService()->createPost($post);

        $this->getThreadService()->deletePost($createdPost['courseId'], $createdPost['id']);

        $foundPosts = $this->getThreadService()->findThreadPosts($createdPost['courseId'], $createdPost['threadId'], 'default', 0, 20);

        $this->assertTrue(is_array($foundPosts));
        $this->assertEmpty($foundPosts);

        $thread = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);
        $this->assertEquals(0, $thread['postNum']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
