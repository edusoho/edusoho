<?php
namespace Mooc\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class ThreadServiceTest extends BaseTestCase
{
    public function testSearchThreadPostCount()
    {
        $course = $this->getCourse();
        $thread = $this->createThread($course);
        $post1  = $this->craeteThreadPost($thread);
        $post2  = $this->craeteThreadPost($thread);
        $post3  = $this->craeteThreadPost($thread);
        $post4  = $this->craeteThreadPost($thread);

        $user       = $this->getCurrentUser();
        $conditions = array('courseId' => $course['id'], 'userId' => $user['id']);
        $postNum    = $this->getThreadService()->searchThreadPostCount($conditions);
        $this->assertEquals(4, $postNum);

        $conditions = array('courseId' => $course['id'], 'userId' => $user['id'], 'threadId' => $thread['id']);
        $postNum    = $this->getThreadService()->searchThreadPostCount($conditions);
        $this->assertEquals(4, $postNum);

        $conditions = array('courseId' => $course['id'], 'userId' => $user['id'], 'threadId' => 2);
        $postNum    = $this->getThreadService()->searchThreadPostCount($conditions);
        $this->assertEquals(0, $postNum);

        /*      ->andWhere('courseId = :courseId')
    ->andWhere('lessonId = :lessonId')
    ->andWhere('threadId = :threadId')
    ->andWhere('userId = :userId')
    ->andWhere('createdTime >= :startTime')
    ->andWhere('createdTime <= :endTime');*/
    }

    private function getCourse()
    {
        return $this->getCourseService()->createCourse(array('title' => 'test course1'));
    }

    private function createThread($course)
    {
        $thread = array(
            'courseId' => $course['id'],
            'type'     => 'discussion',
            'title'    => 'test thread',
            'content'  => 'test content'
        );
        return $this->getThreadService()->createThread($thread);
    }

    private function craeteThreadPost($createdThread)
    {
        $post = array(
            'courseId' => $createdThread['courseId'],
            'threadId' => $createdThread['id'],
            'content'  => 'post thread'
        );
        return $this->getThreadService()->createPost($post);
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }
}
