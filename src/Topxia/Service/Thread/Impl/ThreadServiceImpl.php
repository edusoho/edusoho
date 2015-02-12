<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Thread\ThreadService;
use Topxia\Common\ArrayToolkit;

class ThreadServiceImpl extends BaseService implements ThreadService
{

    public function getThread($threadId)
    {
        return $this->getThreadDao()->getThread($threadId);
    }

    public function findThreadsByType($courseId, $type, $sort = 'latestCreated', $start, $limit)
    {
        if ($sort == 'latestPosted') {
            $orderBy = array('latestPosted', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }

        if (!in_array($type, array('question', 'discussion'))) {
            $type = 'all';
        }

        if ($type == 'all') {
            return $this->getThreadDao()->findThreadsByCourseId($courseId, $orderBy, $start, $limit);
        }

        return $this->getThreadDao()->findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);
    }

    public function findLatestThreadsByType($type, $start, $limit)
    {
        return $this->getThreadDao()->findLatestThreadsByType($type, $start, $limit);
    }

    public function findEliteThreadsByType($type, $status, $start, $limit)
    {
        return $this->getThreadDao()->findEliteThreadsByType($type, $status, $start, $limit);
    }

    public function searchThreads($conditions, $sort, $start, $limit)
    {
        
        $orderBys = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadDao()->searchThreads($conditions, $orderBys, $start, $limit);
    }


    public function searchThreadCount($conditions)
    {    
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadDao()->searchThreadCount($conditions);
    }

    public function searchThreadCountInCourseIds($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadDao()->searchThreadCountInCourseIds($conditions);
    }

    public function searchThreadInCourseIds($conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadDao()->searchThreadInCourseIds($conditions, $orderBys, $start, $limit);
    }
    
    private function filterSort($sort)
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
                    array('lastPostTime', 'DESC'),
                );
                break;
            case 'createdNotStick':
                $orderBys = array(
                    array('createdTime', 'DESC'),
                );
                break;
            case 'postedNotStick':
                $orderBys = array(
                    array('lastPostTime', 'DESC'),
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

    private function prepareThreadSearchConditions($conditions)
    {

        if(empty($conditions['type'])) {
            unset($conditions['type']);
        }

        if(empty($conditions['keyword'])) {
            unset($conditions['keyword']);
            unset($conditions['keywordType']);
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('title', 'content', 'targetId', 'targetTitle'))) {
                throw $this->createServiceException('keywordType参数不正确');
            }
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if(empty($conditions['author'])) {
            unset($conditions['author']);
        }

        if (isset($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        return $conditions;
    }

    public function createThread($thread)
    {   
        $this->tryAccess('thread.create', $thread);

        if (empty($thread['title'])) {
            throw $this->createServiceException("标题名称不能为空！");
        }
        $thread['title'] = $this->purifyHtml(empty($thread['title']) ? '' : $thread['title']);

        if (empty($thread['content'])) {
            throw $this->createServiceException("话题内容不能为空！");
        }
        $thread['content'] = $this->purifyHtml(empty($thread['content']) ? '' : $thread['content']);

        if (empty($thread['targetId'])) {
            throw $this->createServiceException(' Id不能为空！');
        }
        if (empty($thread['type']) or !in_array($thread['type'], array('discussion', 'question'))) {
            throw $this->createServiceException(sprintf('Thread type(%s) is error.', $thread['type']));
        }

        $thread['userId'] = $this->getCurrentUser()->id;

        $thread['createdTime'] = time();
        $thread['updateTime'] = time();
        $thread['lastPostMemberId'] = $thread['userId'];
        $thread['lastPostTime'] = $thread['createdTime'];
        $thread = $this->getThreadDao()->addThread($thread);

        return $thread;
    }

    public function updateThread($targetId, $threadId, $fields)
    {
        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            throw $this->createServiceException('话题不存在，更新失败！');
        }

        $this->tryAccess('thread.update', $thread);

        $thread['updateTime'] = time();

        $user = $this->getCurrentUser();

        $fields = ArrayToolkit::parts($fields, array('title', 'content'));
        if (empty($fields)) {
            throw $this->createServiceException('参数缺失，更新失败。');
        }

        //更新thread过滤html
        $fields['content'] = $this->purifyHtml($fields['content']);
        return $this->getThreadDao()->updateThread($threadId, $fields);
    }

    public function deleteThread($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $threadId));
        }

        $this->tryAccess('thread.delete', $thread);

        $this->getThreadPostDao()->deletePostsByThreadId($threadId);
        $this->getThreadDao()->deleteThread($threadId);

        $this->getLogService()->info('thread', 'delete', "删除话题 {$thread['title']}({$thread['id']})");
    }

    public function stickThread($targetType,$targetId, $threadId)
    {
        $this->tryManage($targetType,$targetId);

        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('isStick' => 1,'updateTime' => time()));
    }

    public function unstickThread($targetType,$targetId, $threadId)
    {
        $this->tryManage($targetType,$targetId);

        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('isStick' => 0,'updateTime' => time()));
    }

    public function eliteThread($targetType,$targetId, $threadId)
    {
        $this->tryManage($targetType,$targetId);

        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('isElite' => 1,'updateTime' => time()));
    }

    public function uneliteThread($targetType,$targetId, $threadId)
    {
        $this->tryManage($targetType,$targetId);

        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('isElite' => 0,'updateTime' => time()));
    }

    public function hitThread($targetId, $threadId)
    {
        $this->getThreadDao()->waveThread($threadId, 'hitNum', +1);
    }

    public function findThreadPosts($targetId, $threadId, $sort = 'default', $start, $limit)
    {
        $thread = $this->getThread($targetId, $threadId);
        if (empty($thread)) {
            return array();
        }
        if ($sort == 'best') {
            $orderBy = array('score', 'DESC');
        } else if($sort == 'elite') {
            $orderBy = array('createdTime', 'DESC', ',isElite', 'ASC');
        } else {
            $orderBy = array('createdTime', 'ASC');
        }

        return $this->getThreadPostDao()->findPostsByThreadId($threadId, $orderBy, $start, $limit);
    }

    public function getThreadPostCount($targetId, $threadId)
    {
        return $this->getThreadPostDao()->getPostCountByThreadId($threadId);
    }

    public function findThreadElitePosts($targetId, $threadId, $start, $limit)
    {
        return $this->getThreadPostDao()->findPostsByThreadIdAndIsElite($threadId, 1, $start, $limit);
    }

    public function getPostCountByuserIdAndThreadId($userId,$threadId)
    {
        return $this->getThreadPostDao()->getPostCountByuserIdAndThreadId($userId,$threadId);
    }

    public function getThreadPostCountByThreadId($threadId)
    {
        return $this->getThreadPostDao()->getPostCountByThreadId($threadId);
    }

    public function getPost($targetId, $id)
    {
        $post = $this->getThreadPostDao()->getPost($id);
        if (empty($post) or $post['targetId'] != $targetId) {
            return null;
        }
        return $post;
    }

    public function createPost($threadContent,$targetType,$targetId,$memberId,$threadId,$parentId=0)
    {          
                        // $targetId = empty($threadContent['targetId']) ? $targsetId : $threadContent['targetId'];
                        $thread = $this->getThread($targetId, $threadId);

                        if (empty($threadContent['content'])) {
                            throw $this->createServiceException("回复内容不能为空！");
                        }
                        $threadContent['content']=$this->purifyHtml($threadContent['content']);
                        $threadContent['userId']=$memberId;
                        $threadContent['fromUserId']=$threadContent['fromUserId'];
                        $threadContent['createdTime']=time();
                        $threadContent['threadId']=$threadId;
                        $threadContent['parentId']=$parentId;
                        $threadContent['targetType']=$targetType;
                        $threadContent['targetId']=$targetId;
                        $post=$this->getThreadPostDao()->addPost($threadContent);  
                        
                        // 高并发的时候， 这样更新postNum是有问题的，这里暂时不考虑这个问题。
                        $threadFields = array(
                            'postNum' => $thread['postNum'] + 1,
                            'lastPostMemberId' => $threadContent['userId'],
                            'lastPostTime' => $threadContent['createdTime'],
                            'updateTime' => time(),
                        );
                        $this->getThreadDao()->updateThread($thread['id'], $threadFields);
                        
                        return $post;




        $requiredKeys = array('targetId', 'threadId', 'content');
        if (!ArrayToolkit::requireds($post, $requiredKeys)) {
            throw $this->createServiceException('参数缺失');
        }

        $thread = $this->getThread($post['targetId'], $post['threadId']);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('课程(ID: %s)话题(ID: %s)不存在。', $post['courseId'], $post['threadId']));
        }

        // list($course, $member) = $this->getCourseService()->tryTakeCourse($post['targetId']);

        $post['userId'] = $this->getCurrentUser()->id;
        // $post['isElite'] = $this->getCourseService()->isCourseTeacher($post['targetId'], $post['userId']) ? 1 : 0;
        $post['createdTime'] = time();

        //创建post过滤html
        $post['content'] = $this->purifyHtml($post['content']);
        $post['parentId']=$parentId;
        $post = $this->getThreadPostDao()->addPost($post);

        // 高并发的时候， 这样更新postNum是有问题的，这里暂时不考虑这个问题。
        $threadFields = array(
            'postNum' => $thread['postNum'] + 1,
            'lastPostMemberId' => $post['userId'],
            'lastPostTime' => $post['createdTime'],
            'updateTime' => time(),
        );
        $this->getThreadDao()->updateThread($thread['id'], $threadFields);

        return $post;
    }

    public function updatePost($targetId, $threadId,$id, $fields)
    {                        
                            $thread = $this->getThread($targetId, $threadId);
        
        $post = $this->getPost($targetId, $id);
        if (empty($post)) {
            throw $this->createServiceException("回帖#{$id}不存在。");
        }

        $user = $this->getCurrentUser();
        // ($user->isLogin() and $user->id == $post['userId']) or $this->getCourseService()->tryManageCourse($courseId);


        $fields  = ArrayToolkit::parts($fields, array('content'));
        if (empty($fields)) {
            throw $this->createServiceException('参数缺失。');
        }

        //更新post过滤html
        $fields['content'] = $this->purifyHtml($fields['content']);

         $threadFields = array(
                            'updateTime' => time(),
        );
        $this->getThreadDao()->updateThread($threadId, $threadFields);

        return $this->getThreadPostDao()->updatePost($id, $fields);
    }

    public function deletePost($targetType,$threadId,$targetId, $id)
    {
        $this->tryManage($targetType,$targetId);
        $thread = $this->getThread($targetId, $threadId);

        $post = $this->getThreadPostDao()->getPost($id);
        if (empty($post)) {
            throw $this->createServiceException(sprintf('帖子(#%s)不存在，删除失败。', $id));
        }

        if ($post['targetId'] != $targetId) {
            throw $this->createServiceException(sprintf('帖子#%s不属于内容#%s，删除失败。', $id, $targetId));
        }

        $this->getThreadPostDao()->deletePost($post['id']);
        $this->getThreadDao()->waveThread($post['threadId'], 'postNum', -1);

         $threadFields = array(
                            'updateTime' => time(),
        );
        $this->getThreadDao()->updateThread($threadId, $threadFields);
    }

    public function searchPostsCount($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);
        $count= $this->getThreadPostDao()->searchPostsCount($conditions);
        return $count;
    }

    public function searchPosts($conditions,$orderBy,$start,$limit)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);
        return $this->getThreadPostDao()->searchPosts($conditions,$orderBy,$start,$limit);

    }

    public function canAccess($permision, $resource)
    {
        $permisions = array(
            'thread.create' => 'accessThreadCreate',
            'thread.read' => 'accessThreadRead',
            'thread.update' => 'accessThreadUpdate',
            'thread.delete' => 'accessThreadDelete',
            'thread.stick' => 'accessThreadStick',
            'thread.nice' => 'accessThreadNice',
            'post.create' => 'accessPostCreate',
            'post.update' => 'accessPostUpdate',
            'post.delete' => 'accessPostDelete',
        );

        if (!array_key_exists($permision, $permisions)) {
            throw new InvalidArgumentException("Permision `{$permision}` is invalide.");
        }

        $firewall = $this->getTargetFirewall($resource);

        $method = $permisions[$permision];

        return $firewall->$method($resource);
    }

    public function tryAccess($permision, $resource)
    {
        if (!$this->canAccess($permision, $resource)) {
            throw $this->createAccessDeniedException("Permision `{$permision}`, resource `{$resource['targetType']}[{$resource['targetId']}]`, access denied.");
        }
    }

    private function getTargetFirewall($resource)
    {
        if (empty($resource['targetType']) or empty($resource['targetId'])) {
            throw new InvalidArgumentException("Resource  targetType or targetId argument missing."); 
        }

        $class = __NAMESPACE__ . "\\" . ucfirst($resource['targetType']) . 'ThreadFirewall';

        return new $class();
    }

    private function getThreadDao()
    {
        return $this->createDao('Thread.ThreadDao');
    }

    private function getThreadPostDao()
    {
        return $this->createDao('Thread.ThreadPostDao');
    }

    private function getUserService()
    {
          return $this->createService('User.UserService');
    }

    private function getNotifiactionService()
    {
          return $this->createService('User.NotificationService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }


}