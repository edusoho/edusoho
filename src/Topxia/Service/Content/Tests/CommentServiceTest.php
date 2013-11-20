<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Content\CommentService;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class CommentServiceTest extends BaseTestCase
{   

    /**
    * @group current
    **/
    public function testCreateCourseComment()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        $createdComment = $this->getCommentService()->createComment($commentInfo);
        
        $this->assertEquals(CommentService::COMMENT_OBJECTTYPE_COURSE, $createdComment['objectType']);
        $this->assertEquals($course['id'], $createdComment['objectId']);
        $this->assertEquals($this->getCurrentUser()->id, $createdComment['userId']);
        $this->assertEquals("content to create", $createdComment['content']);
        
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateCourseCommentWithNotExistObjectId()
    {
        $commentInfo = array(
            "objectType" => CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId" => 999,
            "content" => "notExistComment"
            );
        $this->getCommentService()->createComment($commentInfo);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateCourseCommentWithNotExistObjectType()
    {
        $commentInfo = array(
            "objectType" => 'xxxx',
            "objectId" => 1,
            "content" => "notExistComment"
            );
        $this->getCommentService()->createComment($commentInfo);
    }

    /**
     * @group get
     */
    public function testGetComment()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        $createdComment = $this->getCommentService()->createComment($commentInfo);
        $foundComment = $this->getCommentService()->getComment($createdComment['id']);
        $this->assertEquals($createdComment, $foundComment);    
    }

    public function testGetCommentWithNotExistComment()
    {
        $foundComment = $this->getCommentService()->getComment(999);
        $this->assertNull($foundComment);
    }

    public function testFindComments()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        for ($i=0; $i < 4; $i++) { 
            $this->getCommentService()->createComment($commentInfo);
        }
        $foundComments = $this->getCommentService()->findComments(CommentService::COMMENT_OBJECTTYPE_COURSE, $course['id'], 0, 100);

        $this->assertEquals(4, count($foundComments));
        foreach ($foundComments as $foundComment) {
            $this->assertEquals(CommentService::COMMENT_OBJECTTYPE_COURSE, $foundComment['objectType']);
            $this->assertEquals($course['id'], $foundComment['objectId']);
            $this->assertEquals($this->getCurrentUser()->id, $foundComment['userId']);
        }

    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFindCommentsWithNotExistObjectType()
    {
        $this->getCommentService()->findComments('xxxxx', 999, 0, 100);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFindCommentsWithNotExistObjectId()
    {
        $this->getCommentService()->findComments(CommentService::COMMENT_OBJECTTYPE_COURSE, 999, 0, 100);
    }

    /**
     * @group current
     */
    public function testDeleteComment()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        $createdComment = $this->getCommentService()->createComment($commentInfo);
        $result = $this->getCommentService()->deleteComment($createdComment['id']);
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException Topxia\Service\Common\NotFoundException
     */
    public function testDeleteCommentWithNotExistComment()
    {
        $this->getCommentService()->deleteComment(999);
    }

    public function testGetCommentsCountByType()
    {
       $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        for ($i=0; $i < 4; $i++) { 
            $this->getCommentService()->createComment($commentInfo);
        }
        $commentsCount = $this->getCommentService()->getCommentsCountByType(CommentService::COMMENT_OBJECTTYPE_COURSE);
        $this->assertEquals(4, $commentsCount);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetCommentsCountWithNotExistObjectType()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        for ($i=0; $i < 4; $i++) { 
            $this->getCommentService()->createComment($commentInfo);
        }
        $this->getCommentService()->getCommentsCountByType('xxxx');
    }

    public function testGetCommentsByType()
    {
        $user = $this->createUser('user');
        $course = $this->CreateOnlineCourse();
        $commentInfo = array(
            "objectType"=> CommentService::COMMENT_OBJECTTYPE_COURSE,
            "objectId"=>$course['id'],
            "content"=>"content to create"
            );
        for ($i=0; $i < 4; $i++) { 
            $this->getCommentService()->createComment($commentInfo);
        }
        $foundComments = $this->getCommentService()->getCommentsByType(CommentService::COMMENT_OBJECTTYPE_COURSE, 0, 100);
        $this->assertEquals(4, count($foundComments));
        foreach ($foundComments as $foundComment) {
            $this->assertEquals(CommentService::COMMENT_OBJECTTYPE_COURSE, $foundComment['objectType']);
            $this->assertEquals($course['id'], $foundComment['objectId']);
            $this->assertEquals($this->getCurrentUser()->id, $foundComment['userId']);
            $this->assertEquals('content to create', $foundComment['content']);
        }
    }

    private function CreateOnlineCourse(){
        $course = array(
            'type'=>'online',
            'price' => 1000,
            'title' =>'线下课程',
            'tags'=> array('2'),
            'categoryId'=>8,
            'locationId'=>120112,
            'address'=>"test",
            'status'=>"editing");
        return $this->getCourseService()->CreateCourse($course);
    }

    private function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password']= "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCommentService()
    {
        return $this->getServiceKernel()->createService('Content.CommentService');
    }
}