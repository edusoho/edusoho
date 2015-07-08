<?php
namespace Topxia\Service\Thread\Tests;

use Topxia\Service\Common\BaseTestCase;
//  use Topxia\Service\Common\ServiceException;
use Topxia\Service\User\CurrentUser;

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
    public function testFindThreadsByTargetAndUserId()
    {
      $thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $foundThreads = $this->getThreadService()->findThreadsByTargetAndUserId(array('type' => $thread['targetType'], 'id' => $thread['targetId']), $user['id'], 0, 11);
      $this->assertEquals(1, count($foundThreads));
    }
    public function testFindZeroPostThreadsByTarget()
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
      $Thread = $this->getThreadService()->CreateThread($thread);  
      $thread = array();
      $thread['title'] = 'title2';
      $thread['content'] = 'xxx2';
      $thread['userId'] = $user['id'];
      $thread['targetId'] = $classroom['id'];
      $thread['targetType'] = 'classroom';
      $thread['type'] = 'question';
      $Thread2 = $this->getThreadService()->CreateThread($thread);  
      $post1 = array(
        'id' => '1',
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content1',
        );
      $post2 = array(
        'id' => '2',
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'hhh',
        );
      $createdPost1 = $this->getThreadService()->createPost($post1);
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $zeroThread = $this->getThreadService()->findZeroPostThreadsByTarget(array('type' => $thread['targetType'], 'id' => $thread['targetId']), 0, 11);
      $this->assertEquals(2,$Thread2['id']);
    }
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
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $post2 = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content2',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $createdPost2 = $this->getThreadService()->createPost($post);
      $createdPost=$this->getThreadService()->findThreadPosts($Thread['targetId'],$Thread['id'],'default',0,20);
      $this->assertEquals(2,count($createdPost));
    }
    /**
     * 获得话题回帖的数量
     * @param  integer $courseId 话题的课程ID
     * @param  integer $threadId 话题ID
     * @return integer 话题回帖的数量
     */
    public function testGetThreadPostCount()
    {
      $Thread = $this->CreateProtecThread();

      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $Thread = $this->getThreadService()->getThreadPostCount($Thread['targetId'],$Thread['id']);
      $this->assertEquals('1',$Thread);
    }
     /**
     * 回复话题
     **////
    public function testGetPost()
    {
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
    public function testGetPostPostionInArticle()    
    {  
      $user = $this->createUser();
      $article= array(
        'title'=> 'articletitle',
        'body' => 'hello,im stefanie', 
        'thumb' => '123',
        'originalThumb' =>'123',
        'categoryId'=> '1',
        'source' => 'source',
        'sourceUrl' => ' ',
        'picture' => 'asdf',
        'status' => 'published',
        'userId' => $user['id'],
        'publishedTime' => time(),
        'tags' => '1',
      );
      $article = $this->getArticleService()->createArticle($article);
      $post3 = array(
        'id' => '1',
        'targetType' => 'article',
        'targetId' => $article['id'],
        'content' => 'post content',
        );

      $createdPost1 = $this->getThreadService()->createPost($post3);
      
      $result = $this->getThreadService()->getPostPostionInArticle($article['id'],$post3['id']);
      $this->assertEquals(1, $result);;  
    }
    public function testFindPostsByParentId()
    {
      $Thread = $this->CreateProtecThread();
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $post2 = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'child post',
        'parentId' => $createdPost['id'],
        );
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $findPost = $this->getThreadService()->findPostsByParentId($createdPost['id'],0,20);

      $this->assertEquals(1,count($findPost));
    } 
    public function testFindPostsCountByParentId()
    {  
      $Thread = $this->CreateProtecThread();
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $post2 = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'child post2',
        'parentId' => $createdPost['id'],
        );
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $post3 = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'child post3',
        'parentId' => $createdPost['id'],
        );
      $createdPost3 = $this->getThreadService()->createPost($post3);
      $post4 = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'child post4',
        'parentId' => $createdPost['id'],
        );
      $createdPost4 = $this->getThreadService()->createPost($post4);
      $findPostCount = $this->getThreadService()->findPostsCountByParentId($createdPost['id']);
      $this->assertEquals(3, $findPostCount);
    }

    public function testCreatePost()
  	{   
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
    public function testSearchPosts()
    {
      $Thread = $this->CreateProtecThread();

      $post1 = array(
        'id' => '1',
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content1',
        );
      $post2 = array(
        'id' => '2',
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'eeeeeee',
        );
      $post3 = array(
        'id' => '3',
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'create',
        );
      $createdPost1 = $this->getThreadService()->createPost($post1);
      $createdPost2 = $this->getThreadService()->createPost($post2);
      $createdPost3 = $this->getThreadService()->createPost($post3);
      $sort = 'created';
      $orderBys = $this->filterSort($sort);
      $conditions = array('targetId' => $Thread['id']);
      $foundPosts = $this->getThreadService()->searchPosts($conditions, $orderBys, 0, 20);
      $this->assertEquals(3, count($foundPosts));
    }
    public function testVoteUpPost()
    {
      $Thread = $this->CreateProtecThread();
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $result = $this->getThreadService()->voteUpPost($createdPost['id']);
      $this->assertEquals('ok', $result['status']);

    }
    public function testSetPostAdopted()
    {
      $Thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $this->getThreadService()->setPostAdopted($createdPost['id']);
      $foundPost = $this->getThreadService()->getPost($createdPost['id']);
      $this->assertEquals('1', $foundPost['adopted']);
    } 

    
    public function testCancelPostAdopted()
    {
      $Thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $post = array(
        'targetType' => $Thread['targetType'],
        'targetId' => $Thread['targetId'],
        'threadId' => $Thread['id'],
        'content' => 'post content',
        );
      $createdPost = $this->getThreadService()->createPost($post);
      $this->getThreadService()->setPostAdopted($createdPost['id']);
      $foundPost = $this->getThreadService()->getPost($createdPost['id']);
      $this->assertEquals('1', $foundPost['adopted']);
      $this->getThreadService()->cancelPostAdopted($foundPost['id']);
      $result = $this->getThreadService()->getPost($foundPost['id']);
      $this->assertEquals('0', $result['adopted']);
    }
    public function testCanAccess()
    {
        $thread = $this->CreateProtecThread();
        $this->getThreadService()->canAccess('thread.create',$thread);
    }
    public function testTryAccess()
    {
        $thread = $this->CreateProtecThread();
        $this->getThreadService()->tryAccess('thread.create',$thread);
      
    }

    /**
     * 话题成员
     **/
    public function testFindMembersCountByThreadId()
    {
      $currentUser = new CurrentUser();
      $currentUser->fromArray(array(
          'id' => 2,
          'nickname' => 'user',
          'email' => 'user@user.com',
          'password' => 'user',
          'currentIp' => '127.0.0.1',
          'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
      ));
      $this->getServiceKernel()->setCurrentUser($currentUser);
      $Thread = $this->CreateProtecThread();
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $currentUser['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $currentUser2 = new CurrentUser();
      $currentUser2->fromArray(array(
          'id' => 1,
          'nickname' => 'user2',
          'email' => 'user2@user.com',
          'password' => 'user',
          'currentIp' => '127.0.0.1',
          'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
      ));
      $this->getServiceKernel()->setCurrentUser($currentUser2);
      $fields2 = array(
        'threadId' => $Thread['id'],
        'userId' => $currentUser2['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields2);
      $memberCount = $this->getThreadService()->findMembersCountByThreadId($Thread['id']);
      $this->assertEquals(2, $memberCount);
    }
    public function testFindMembersByThreadId()
    {
      $currentUser = new CurrentUser();
      $currentUser->fromArray(array(
          'id' => 2,
          'nickname' => 'user',
          'email' => 'user@user.com',
          'password' => 'user',
          'currentIp' => '127.0.0.1',
          'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
      ));
      $this->getServiceKernel()->setCurrentUser($currentUser);
      $Thread = $this->CreateProtecThread();
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $currentUser['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $findMember = $this->getThreadService()->findMembersByThreadId($Thread['id'],0,11);
      $this->assertEquals(1, count($findMember));

    }
  
    public function testGetMemberByThreadIdAndUserId()
    {
      $Thread = $this->CreateProtecThread();
      $currentUser = new CurrentUser();
      $currentUser->fromArray(array(
          'id' => 1,
          'nickname' => 'user',
          'email' => 'user@user.com',
          'password' => 'user',
          'currentIp' => '127.0.0.1',
          'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
      ));
      $this->getServiceKernel()->setCurrentUser($currentUser);
      
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $currentUser['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $result = $this->getThreadService()->getMemberByThreadIdAndUserId($Thread['id'],$currentUser['id']);
      $this->assertEquals('xiaofang', $result['nickname']);
    }
    
    public function testFindMembersByThreadIdAndUserIds()
    {
      $Thread = $this->CreateProtecThread();
      $currentUser = $this->getCurrentUser();
      
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $currentUser['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields); 
      $result = $this->getThreadService()->findMembersByThreadIdAndUserIds($Thread['id'],array($currentUser['id']));
      $this->assertEquals('1', count($result));
    }
    public function testCreateMember()
    {
      $Thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $user['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $this->assertEquals('xiaofang', $member['nickname']);
      
    }
    public function testDeleteMember()
    {
      $Thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $user['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $this->assertEquals('xiaofang', $member['nickname']);
      $member = $this->getThreadService()->deleteMember($member['id']);
      $this->assertEmpty($member);
    }
    public function testDeleteMembersByThreadId()
    {
     
      $Thread = $this->CreateProtecThread();
      $user = $this->getCurrentUser();
      $fields = array(
        'threadId' => $Thread['id'],
        'userId' => $user['id'],
        'nickname' => 'xiaofang'
       );
      $member = $this->getThreadService()->createMember($fields);
      $this->assertEquals('xiaofang', $member['nickname']);
      $member = $this->getThreadService()->DeleteMembersByThreadId($Thread['id']);
      $this->assertEmpty($member);
    }
    /**
    * 扩展API
    */

    // public function testSetUserBadgeTitle()
    // {
      
    //   $currentUser = new CurrentUser();
    //   $currentUser->fromArray(array(
    //       'id' => 2,
    //       'nickname' => 'user',
    //       'email' => 'user@user.com',
    //       'password' => 'user',
    //       'currentIp' => '127.0.0.1',
    //       'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
    //   ));
    //   $this->getServiceKernel()->setCurrentUser($currentUser);

    //     $textClassroom = array(
    //         'title' => 'test',
    //     );
    //     $classroom = $this->getClassroomService()->addClassroom($textClassroom);

    //     $thread = array();
    //     $thread['title'] = 'title';
    //     $thread['content'] = 'xxx';
    //     $thread['userId'] = $currentUser['id'];
    //     $thread['targetId'] = $classroom['id'];
    //     $thread['targetType'] = 'classroom';
    //     $thread['type'] = 'question';
    //     $Thread = $this->getThreadService()->CreateThread($thread);

    //     $this->getThreadService()->setUserBadgeTitle($Thread,array($currentUser));
    //     $a = $this->getUserService()->register($currentUser); 
    //    var_dump($a['badgeTitle']);
    // }

    public function testFindTeacherIds()
    {     
      $user = new CurrentUser();
      $user->fromArray(array(
          'id' => 1,
          'nickname' => 'user',
          'email' => 'user@user.com',
          'password' => 'user',
          'currentIp' => '127.0.0.1',
          'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER'),
      ));
      $this->getServiceKernel()->setCurrentUser($user);
      $textClassroom = array(
            'title' => 'test',
        );
      $classroom = $this->getClassroomService()->addClassroom($textClassroom);
      $this->getClassroomService()->updateClassroomTeachers($classroom['id']);
        $b = $this->getClassroomService()->isClassroomTeacher($classroom['id'],$user['id']);
  var_dump($b);
        $thread = array();
        $thread['title'] = 'title';
        $thread['content'] = 'xxx';
        $thread['userId'] = $user['id'];
        $thread['targetId'] = $classroom['id'];
        $thread['targetType'] = 'classroom';
        $thread['type'] = 'question';
        $Thread = $this->getThreadService()->createThread($thread);
        // $this->getThreadService()->setThreadSolved($Thread['id']);
        // $result = $this->getThreadService()->getThread($Thread['id']);
        $hello = $this->getThreadService()->findTeacherIds($Thread); 
        var_dump($hello);
    }





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
    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
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