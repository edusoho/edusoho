<?php

namespace Topxia\Service\Group\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Group\GroupService;
use Topxia\Service\Group\ThreadService;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;

class ThreadServiceImpl extends BaseService implements ThreadService {

    public function getThread($id)
    {
        return $this->getThreadDao()->getThread($id);
    }

    public function getThreadCount($conditions)
    {
        $conditions=$this->prepareThreadConditions($conditions);
        $count=$this->getThreadDao()->searchThreadsCount($conditions);
        return $count;
    }

    public function getPost($id)
    {
         return $this->getThreadPostDao()->getPost($id);
    }

    public function addThread($thread) 
    {     
        if (empty($thread['title'])) {
            throw $this->createServiceException("标题名称为空！");
        }
        $thread=$this->getThreadDao()->addThread($thread);

        $this->getGroupService()->waveGroup($thread['groupId'],'threadNum',+1);

        $this->getGroupService()->waveMember($thread['groupId'],$thread['userId'],'threadNum',+1);
        
        return $thread;
    }

    public function closeThread($threadId)
    {
         $this->getThreadDao()->updateThread($threadId,array('enum'=>'close'));
    }

    public function openThread($threadId)
    {
         $this->getThreadDao()->updateThread($threadId,array('enum'=>'open'));
    }

    public function searchThreads($conditions,$orderBy,$start, $limit)
    {
        $conditions=$this->prepareThreadConditions($conditions);
        return $this->getThreadDao()->searchThreads($conditions,$orderBy,$start,$limit);
    }

    public function postThread($threadContent,$groupId,$memberId,$threadId)
    {
        $user=$this->getCurrentUser();
        if (empty($threadContent['content'])) {
            throw $this->createServiceException("回复内容为空！");
        }
        $post=$this->getThreadPostDao()->addPost($threadContent);  
        $this->getThreadDao()->updateThread($threadId,array('lastPostMemberId'=>$memberId,'lastPostTime'=>time()));
        $this->getGroupService()->waveGroup($groupId,'postNum',+1);
        $this->getGroupService()->waveMember($groupId,$memberId,'postNum',+1);
        $this->waveThread($threadId,'postNum',+1);
        $thread=$this->getThread($threadId); 
        return $post;
    }

    public function searchPosts($conditions,$orderBy,$start,$limit)
    {
        $conditions = $this->prepareThreadConditions($conditions);
        return $this->getThreadPostDao()->searchPosts($conditions,$orderBy,$start,$limit);

    }

    public function searchPostsCount($conditions)
    {
        $conditions = $this->prepareThreadConditions($conditions);
        $count= $this->getThreadPostDao()->searchPostsCount($conditions);
        return $count;
    }

    public function setElite($threadId)
    {
        $this->getThreadDao()->updateThread($threadId,array('isElite'=>1));
    }

    public function removeElite($threadId)
    {
        $this->getThreadDao()->updateThread($threadId,array('isElite'=>0));
    }

    public function setStick($threadId)
    {
        $this->getThreadDao()->updateThread($threadId,array('isStick'=>1));
    }

    public function removeStick($threadId)
    {
        $this->getThreadDao()->updateThread($threadId,array('isStick'=>0));
    }

    public function deleteThread($threadId)
    {
         $thread=$this->getThreadDao()->getThread($threadId);
         $this->deletePostsByThreadId($threadId);
         $this->getThreadDao()->deleteThread($threadId);

         $this->getGroupService()->waveGroup($thread['groupId'],'threadNum',-1);

         $this->getGroupService()->waveMember($thread['groupId'],$threadId,'threadNum',-1);

    }

    public function deletePost($postId)
    {
        $post=$this->getThreadPostDao()->getPost($postId);
        $threadId=$post['threadId'];
        $thread=$this->getThreadDao()->getThread($threadId);

        $this->getThreadPostDao()->deletePost($postId);

        $this->getGroupService()->waveGroup($thread['groupId'],'postNum',-1);

        $this->getGroupService()->waveMember($thread['groupId'],$threadId,'postNum',-1);

        $this->waveThread($threadId,'postNum',-1);
 
    }

    public function deletePostsByThreadId($threadId)
    {
        $thread=$this->getThreadDao()->getThread($threadId);
        $postCount=$this->getThreadPostDao()->searchPostsCount(array('threadId'=>$threadId));

        $this->getGroupService()->waveGroup($thread['groupId'],'postNum',-$postCount);

        $this->getGroupService()->waveMember($thread['groupId'],$threadId,'postNum',-$postCount);

        $this->getThreadPostDao()->deletePostsByThreadId($threadId);
    }

    private function waveThread($id,$field, $diff)
    {
        return $this->getThreadDao()->waveThread($id, $field, $diff);

    }
    private function getLogService() 
    {
        return $this->createService('System.LogService');
    }

     private function prepareThreadConditions($conditions)
     {
        if(isset($conditions['groupName'])&&$conditions['groupName']!==""){
            $group=$this->getGroupService()->findGroupsByTitle($conditions['groupName']);
            if(!empty($group)){
              $conditions['groupId']=$group[0]['id'];  
            }   
        }

         if(isset($conditions['enum']))
        {
            if($conditions['enum']==""){
               unset( $conditions['enum']);
            }
        }   
        return $conditions;
    }

    private function getThreadDao()
    {
        return $this->createDao('Group.ThreadDao');
    }
    private function getGroupService()
    {
        return $this->createService('Group.GroupService');
    }
    private function getThreadPostDao()
    {
        return $this->createDao('Group.ThreadPostDao');
    }
    private function getMessageService() 
    {
        return $this->createService('User.MessageService');
    }
}
