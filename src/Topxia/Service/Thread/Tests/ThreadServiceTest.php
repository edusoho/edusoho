<?php
namespace Topxia\Service\Thread\Tests;

use Topxia\Service\Common\BaseTestCase;
//  use Topxia\Service\Common\ServiceException;

class ThreadServiceTest extends BaseTestCase
{
    /**
    * 基础API
    */
    public function testGetThread()
    {
        $Thread = $this->CreateProtecThread();
        $foundThread = $this->getThreadService()->getThread($Thread['id']);
        $this->assertEquals('title', $foundThread['title']);
        $this->assertEquals('xxx', $foundThread['content']);
    }
    public function testSearchThreads()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread1 = array();
        $thread1['title'] = 'title';
        $thread1['content'] = 'xxx';
        $thread1['userId'] = $user['id'];
        $thread1['targetId'] = $classroom['id'];
        $thread1['targetType'] = 'classroom';
        $thread1['type'] = 'question';
        $threadNew1 = $this->getThreadService()->CreateThread($thread1);
        $thread2 = array();
        $thread2['title'] = 'title';
        $thread2['content'] = 'xxx';
        $thread2['userId'] = $user['id'];
        $thread2['targetId'] = $classroom['id'];
        $thread2['targetType'] = 'classroom';
        $thread2['type'] = 'discussion';
        $threadNew2 = $this->getThreadService()->CreateThread($thread2);

        $conditions = array('targetId' => $classroom['id']);
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(2, count($foundThreads));
        $conditions = array('targetId' => $classroom['id'], 'type' => 'discussion');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));
    }
    public function testSearchThreadCount()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread1 = array();
        $thread1['title'] = 'title';
        $thread1['content'] = 'xxx';
        $thread1['userId'] = $user['id'];
        $thread1['targetId'] = $classroom['id'];
        $thread1['targetType'] = 'classroom';
        $thread1['type'] = 'question';
        $threadNew1 = $this->getThreadService()->CreateThread($thread1);
        $thread2 = array();
        $thread2['title'] = 'hello';
        $thread2['content'] = 'xxx';
        $thread2['userId'] = $user['id'];
        $thread2['targetId'] = $classroom['id'];
        $thread2['targetType'] = 'classroom';
        $thread2['type'] = 'discussion';
        $threadNew2 = $this->getThreadService()->CreateThread($thread2);

        $conditions = array('type' => 'question');
        $foundThreads = $this->getThreadService()->searchThreads($conditions, 'created', 0, 20);
        $this->assertEquals(1, count($foundThreads));
    }
    // public function testFindThreadsByTargetAndUserId()
    // {
    //     $user = $this->createUser();
    //     $textClassroom = array(
    //         'title' => 'test',
    //     );
    //     $classroom = $this->getClassroomService()->addClassroom($textClassroom);

    //     $thread1 = array();
    //     $thread1['title'] = 'title';
    //     $thread1['content'] = 'xxx';
    //     $thread1['userId'] = $user['id'];
    //     $thread1['targetId'] = $classroom['id'];
    //     $thread1['targetType'] = 'classroom';
    //     $thread1['type'] = 'question';
       
    //     $threadNew1 = $this->getThreadService()->CreateThread($thread1);

       
    //     $foundThreads = $this->getThreadService()->findThreadsByTargetAndUserId($thread1['targetId'], $user['id'], 0, 20);
    //     $this->assertEquals(2, count($foundThreads));
    // }
    /**
     * 创建话题
     */
    public function testCreateThread()
    { 
      $Thread = $this->CreateProtecThread();

      $this->assertEquals('title', $Thread['title']);
      $this->assertEquals('xxx', $Thread['content']);        
    }
    public function testUpdateThread()
    {
      $Thread = $this->CreateProtecThread();
      $fields = array(
        'title' => 'title2',
        'content' =>'hello123',
        );
      $Thread = $this->getThreadService()->updateThread($Thread['id'],$fields);
      $this->assertEquals($fields['title'],$Thread['title']);

    }  
    public function testDeleteThread()
    {
      $Thread = $this->CreateProtecThread();
      $post = array(
          'targetId' => $Thread['targetId'],
          'targetType' => $Thread['targetType'],
          'threadId' => $Thread['id'],
          'content' => 'post thread',
      );
      $createdPost = $this->getThreadService()->createPost($post);
      $this->getThreadService()->deleteThread($Thread['id']);
      $foundThread = $this->getThreadService()->getThread($Thread['id']);
      $this->assertNull($foundThread);
    }
    public function testSetThreadSticky()
    {
      $thread = $this->CreateProtecThread();  
      $this->getThreadService()->setThreadSticky($thread['id']);
      $result = $this->getThreadService()->getThread($thread['id']);
      $this->assertEquals(1,$result['sticky']);
    }
    public function testCancelThreadSticky()
    {
      $Thread = $this->CreateProtecThread();
      $this->getThreadService()->setThreadSticky($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['sticky']);
      $this->getThreadService()->cancelThreadSticky($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(0,$result['sticky']);
    }
    public function testSetThreadNice()
    {
      $Thread = $this->CreateProtecThread();
      $this->getThreadService()->setThreadNice($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['nice']);
    }
    public function testCancelThreadNice()
    {
      $Thread = $this->CreateProtecThread();
      $this->getThreadService()->setThreadNice($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['nice']);
      $this->getThreadService()->cancelThreadNice($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(0,$result['nice']);
    }
    public function testSetThreadSolved()
    {
      $Thread = $this->CreateProtecThread();
      $this->getThreadService()->setThreadSolved($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['solved']);
    }
    public function testCancelThreadSolved()
    {
      $Thread = $this->CreateProtecThread();
      $this->getThreadService()->setThreadSolved($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['solved']);
      $this->getThreadService()->cancelThreadSolved($Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(0,$result['solved']);
    }
     /**
     * 点击查看话题
     *
     * 此方法，用来增加话题的查看数。
     *
     * @param integer $courseId 课程ID
     * @param integer $threadId 话题ID
     *
     */
    public function testHitThread()
    {
      $Thread = $this->CreateProtecThread();  
      $this->getThreadService()->hitThread($Thread['targetId'],$Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(1,$result['hitNum']);
      $this->getThreadService()->hitThread($Thread['targetId'],$Thread['id']);
      $this->getThreadService()->hitThread($Thread['targetId'],$Thread['id']);
      $this->getThreadService()->hitThread($Thread['targetId'],$Thread['id']);
      $result = $this->getThreadService()->getThread($Thread['id']);
      $this->assertEquals(4,$result['hitNum']);
    }
     /**
     * 获得话题的回帖
     *
     * @param integer $courseId 话题的课程ID
     * @param integer $threadId 话题ID
     * @param string  $sort     排序方式： defalut按帖子的发表时间顺序；best按顶的次序排序。
     * @param integer $start    开始行数
     * @param integer $limit    获取数据的限制行数
     *
     * @return array 获得的话题回帖列表。
     */
    public function testFindThreadPosts()
    {
      $Thread = $this->CreateProtecThread();  
      // $sort = 'created';
      // $orderBys = $this->filterSort($sort);      
      $Thread = $this->getThreadService()->findThreadPosts($Thread['targetId'],$Thread['id'],'eilte',0,20);
      
    }
     /**
     * 回复话题
     **/
    public function testGetPost()
    {
      $user = $this->getCurrentUser();
      $Thread = $this->CreateProtecThread();

      $post = array(
        'id' => '1',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $foundPost = $this->getThreadService()->getPost($createdPost['id']);
      $this->assertEquals($post['content'],$foundPost['content']);
    }
    public function testGetPostPostionInThread()
    {
      $user = $this->getCurrentUser();
      $Thread = $this->CreateProtecThread();
      $post1 = array(
        'id' => '1',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $post2 = array(
        'id' => '2',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost1 = $this->getThreadService()->createPost($post1);
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $getPostion = $this->getThreadService()->getPostPostionInThread($createdPost2['id']);
      $this->assertEquals(2,$getPostion);

    }
    // public function testGetPostPostionInArticle()
    // {

    // }
    public function testCreatePost()
  	{
      $user = $this->getCurrentUser();
      $Thread = $this->CreateProtecThread();

      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $this->assertTrue(is_array($createdPost));
      $this->assertEquals($post['targetId'], $createdPost['targetId']);
      $this->assertEquals($post['threadId'], $createdPost['threadId']);

      $thread = $this->getThreadService()->getThread($post['targetId'], $post['threadId']);
      $this->assertEquals(1, $thread['postNum']);
    }
    public function testDeletePost()
    {
      $Thread = $this->CreateProtecThread();
      $post = array(
          'targetId' => $Thread['targetId'],
          'targetType' => $Thread['targetType'],
          'threadId' => $Thread['id'],
          'content' => 'post thread',
      );
      $createdPost = $this->getThreadService()->createPost($post);
      $this->getThreadService()->deletePost($createdPost['targetId'], $createdPost['id']);
      $foundPosts = $this->getThreadService()->findThreadPosts($createdPost['targetId'], $createdPost['threadId'], 'default', 0, 20);

      $this->assertTrue(is_array($foundPosts));
      $this->assertEmpty($foundPosts);

      $thread = $this->getThreadService()->getThread($post['targetId'], $post['threadId']);
      $this->assertEquals(0, $thread['postNum']);
    }
    public function testSearchPostsCount()
    {
      $user = $this->getCurrentUser();
      $Thread = $this->CreateProtecThread();

      $post1 = array(
        'id' => '1',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content1',
        );
      $post2 = array(
        'id' => '2',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'abc',
        );
      $post3 = array(
        'id' => '3',
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'efd',
        );
      $createdPost1 = $this->getThreadService()->createPost($post1);
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $createdPost3 = $this->getThreadService()->createPost($post3);
      $conditions = array('threadId' => $Thread['id']);
      $count= $this->getThreadService()->SearchPostsCount($conditions);
      $this->assertEquals(3, $count);
      $this->assertEquals(1, count($count));
      $conditions = array('threadId' => $Thread['id'],'id'=>'2');
      $count= $this->getThreadService()->SearchPostsCount($conditions);
      $this->assertEquals(1, $count);
    }
    // public function testSearchPosts()////??
    // {
    //   $user = $this->getCurrentUser();
    //   $Thread = $this->CreateProtecThread();

    //   $post1 = array(
    //     'id' => '1',
    //     'targetId' => $Thread['targetId'],
    //     'threadId' => $Thread['id'],
    //     'content' => 'post content1',
    //     );
    //   $post2 = array(
    //     'id' => '2',
    //     'targetId' => $Thread['targetId'],
    //     'threadId' => $Thread['id'],
    //     'content' => 'hhh',
    //     );
    //   $post3 = array(
    //     'id' => '3',
    //     'targetId' => $Thread['targetId'],
    //     'threadId' => $Thread['id'],
    //     'content' => 'create',
    //     );
    //   $createdPost1 = $this->getThreadService()->createPost($post1);
    //   $createdPost2 = $this->getThreadService()->createPost($post2);
    //   $createdPost3 = $this->getThreadService()->createPost($post3);
    //   $sort = 'created';
    //   $orderBys = $this->filterSort($sort);
    //   $conditions = array('targetId' => $Thread['id']);
    //   $foundPosts = $this->getThreadService()->searchPosts($conditions, $orderBys, 0, 20);
    //   $this->assertEquals(3, count($foundPosts));
    // }
    // public function testVoteUpPost()
    // {

    // }
    //   $conditions1 = array('content'=> 'create');/////////
    //   $foundPosts2 = $this->getThreadService()->searchPosts($conditions1, $orderBys, 0, 20);
    //   $this->assertEquals(2, count($foundPosts2));
    // }
    /**
     * 话题成员
     **/
    // public function testCreateMember()
    // {

    // }



    protected function filterSort($sort)
    {
      switch ($sort) {
        case 'created':
          $orderBys = array(
            array('isStick', 'DESC'),
            array('createdTime', 'DESC'),
          );
          break;
        case 'posted':
          $orderBys = array(
            array('isStick', 'DESC'),
            array('latestPostTime', 'DESC'),
          );
          break;
        case 'createdNotStick':
          $orderBys = array(
            array('createdTime', 'DESC'),
          );
          break;
        case 'postedNotStick':
          $orderBys = array(
            array('latestPostTime', 'DESC'),
          );
          break;
        case 'popular':
          $orderBys = array(
            array('hitNum', 'DESC'),
          );
          break;

        default:
          throw $this->createServiceException('参数sort不正确。');
      }
      return $orderBys;
    }
    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }
    protected function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        $user = $this->getUserService()->register($user); 
    }
    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    protected function getNotifiactionService()
    {
        return $this->createService('User.NotificationService');
    }
    protected function CreateProtecThread()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $thread = array();
        $thread['title'] = 'title';
        $thread['content'] = 'xxx';
        $thread['userId'] = $user['id'];
        $thread['targetId'] = $classroom['id'];
        $thread['targetType'] = 'classroom';
        $thread['type'] = 'question';
        $threadNew = $this->getThreadService()->CreateThread($thread);
        return $threadNew;
    }
}