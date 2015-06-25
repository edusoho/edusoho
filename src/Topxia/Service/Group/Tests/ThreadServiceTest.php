<?php

namespace Topxia\Service\Group\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class ThreadServiceTest extends BaseTestCase
{

    public function testAddThread()
    {   
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->assertEquals($testThread['title'],$thread['title']);
        $this->assertEquals($testThread['content'],$thread['content']);
        $this->assertEquals($testThread['groupId'],$thread['groupId']);
        $this->assertEquals($testThread['userId'],$thread['userId']);

    }
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAddThreadWithEmptyTitle()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
    }
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAddThreadWithEmptyContent()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'xxx',
                'content'=>'',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
    }
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAddThreadWithEmptyGroupId()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'xxx',
                'content'=>'xxx',
                'groupId'=>'',
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
    }
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAddThreadWithEmptyUserId()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>'');

        $thread=$this->getThreadService()->addThread($testThread);
    }

    public function testGetThread()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $thread1=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals($thread,$thread1);

    }

    public function testSearchThreads()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
        $testThread1=array(
                'title'=>'test1',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread1=$this->getThreadService()->addThread($testThread1);

        $threads=$this->getThreadService()->searchThreads(array('title'=>'test1'),array(array('isStick','desc')),0,10);
        $this->assertCount(1,$threads);
        $this->assertEquals($thread1,$threads[0]);
    }

    public function testSearchThreadsCount()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
        $testThread1=array(
                'title'=>'test1',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread1=$this->getThreadService()->addThread($testThread1);

        $count=$this->getThreadService()->searchThreadsCount(array('title'=>'test'));
        $this->assertEquals(2,$count);
    }

    public function testGetThreadsByIds()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);
        $testThread1=array(
                'title'=>'test1',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread1=$this->getThreadService()->addThread($testThread1);

        $threads=$this->getThreadService()->getThreadsByIds(array($thread['id'],$thread1['id']));
        
        $this->assertEquals($thread['title'],$threads[$thread['id']]['title']);
        $this->assertEquals($thread1['title'],$threads[$thread1['id']]['title']);
        $this->assertEquals($thread['groupId'],$threads[$thread['id']]['groupId']);
        $this->assertEquals($thread1['groupId'],$threads[$thread1['id']]['groupId']);
    }

    public function testCloseThread()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('close',$thread['status']);
    }

    public function testOpenThread()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'test',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->closeThread($thread['id']);

        $this->getThreadService()->openThread($thread['id']);

        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals('open',$thread['status']);
    }

    public function testPostThread()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $post=$this->getThreadService()->postThread(array( 'fromUserId' => $user['id'], 'content'=>"xxaaaaa"),$group['id'],$user['id'],$thread['id']);

        $this->assertEquals('xxaaaaa',$post['content']);

    }

    public function testGetPost()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $post=$this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content'=>"xxaaaaa"),$group['id'],$user['id'],$thread['id']);

        $post1=$this->getThreadService()->getPost($post['id']);

        $this->assertEquals($post,$post1);
    }
    /**
    * @group current
    */ 
    public function testSearchPosts()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $post=$this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content'=>'aaaaaa',),$group['id'],$user['id'],$thread['id']);
        $post1=$this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content'=>"test1"),$group['id'],$user['id'],$thread['id']);

        $posts=$this->getThreadService()->searchPosts(array('userId'=>$user['id']),array('createdTime' , 'DESC'),0,10);
        $this->assertCount(2,$posts);
    }

    public function testDeletePost()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $post=$this->getThreadService()->postThread(array('fromUserId' => $user['id'], 'content'=>'aaaaaa',),$group['id'],$user['id'],$thread['id']);
       
        $this->getThreadService()->deletePost($post['id']);

        $post=$this->getThreadService()->getPost($post['id']);

        $this->assertEquals(null,$post);
    }

    public function testDeleteThread()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->deleteThread($thread['id']);

        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(null,$thread);
    }

    public function testSetElite()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1,$thread['isElite']);

    }

    public function testRemoveElite()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setElite($thread['id']);
        $this->getThreadService()->removeElite($thread['id']);
        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(0,$thread['isElite']);

    }

    public function testSetStick()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setStick($thread['id']);
        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(1,$thread['isStick']);

    }
     /**
     *@group current
     */
    public function testRemoveStick()
    {
        $user=$this->createUser();
        $textGroup = array(
            'title' => 'textgroup',
            'about'=>"aaaaaa",
        );
        $group = $this->getGroupService()->addGroup($user,$textGroup);
        $testThread=array(
                'title'=>'test',
                'content'=>'xxx',
                'groupId'=>$group['id'],
                'userId'=>$user['id']);

        $thread=$this->getThreadService()->addThread($testThread);

        $this->getThreadService()->setStick($thread['id']);
        $this->getThreadService()->removeStick($thread['id']);
        $thread=$this->getThreadService()->getThread($thread['id']);

        $this->assertEquals(0,$thread['isStick']);

    }
    protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }
    protected function createUser(){
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        return $this->getUserService()->register($user);
    }

    protected function createUser1(){
        $user = array();
        $user['email'] = "user1@user1.com";
        $user['nickname'] = "user1";
        $user['password']= "user1";
        return $this->getUserService()->register($user);
    }

}