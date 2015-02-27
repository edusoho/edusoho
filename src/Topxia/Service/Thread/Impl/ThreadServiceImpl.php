<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Thread\ThreadService;
use Topxia\Service\Common\ServiceEvent;
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

    public function findThreadsByTargetAndUserId($target, $userId, $start, $limit)
    {
        return $this->getThreadDao()->findThreadsByTargetAndUserId($target, $userId, $start, $limit);
    }

    public function findZeroPostThreadsByTarget($target, $start, $limit)
    {
        return $this->getThreadDao()->findThreadsByTargetAndPostNum($target, 0, $start, $limit);
    }
    
    private function filterSort($sort)
    {
        switch ($sort) {
            case 'created':
                $orderBys = array(
                    array('sticky', 'DESC'),
                    array('createdTime', 'DESC'),
                );
                break;
            case 'posted':
                $orderBys = array(
                    array('sticky', 'DESC'),
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
        $thread['lastPostUserId'] = $thread['userId'];
        $thread['lastPostTime'] = $thread['createdTime'];
        $thread = $this->getThreadDao()->addThread($thread);

        $this->dispatchEvent('thread.create', $thread);

        return $thread;
    }

    public function updateThread($id, $fields)
    {
        $thread = $this->getThread($id);
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
        return $this->getThreadDao()->updateThread($id, $fields);
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

        $this->dispatchEvent('thread.delete', $thread);

        $this->getLogService()->info('thread', 'delete', "删除话题 {$thread['title']}({$thread['id']})");
    }

    public function setThreadSticky($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.sticky', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('sticky' => 1,'updateTime' => time()));

        $this->dispatchEvent('thread.sticky', new ServiceEvent($thread, array('sticky' => 'set')));
    }

    public function cancelThreadSticky($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.sticky', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('sticky' => 0,'updateTime' => time()));

        $this->dispatchEvent('thread.sticky', new ServiceEvent($thread, array('sticky' => 'cancel')));

    }

    public function setThreadNice($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.nice', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('nice' => 1,'updateTime' => time()));

        $this->dispatchEvent('thread.nice', new ServiceEvent($thread, array('nice' => 'set')));
    }

    public function cancelThreadNice($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.nice', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('nice' => 0,'updateTime' => time()));

        $this->dispatchEvent('thread.nice', new ServiceEvent($thread, array('nice' => 'cancel')));
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

    public function getPost($id)
    {
        return $this->getThreadPostDao()->getPost($id);
    }

    public function findPostsByParentId($parentId, $start, $limit)
    {
        return $this->getThreadPostDao()->findPostsByParentId($parentId, $start, $limit);
    }

    public function findPostsCountByParentId($parentId)
    {
        return $this->getThreadPostDao()->findPostsCountByParentId($parentId);
    }

    public function createPost($fields)
    {
        $user = $this->getCurrentUser();
        $thread = $this->getThread($fields['threadId']);

        $fields['targetType'] = $thread['targetType'];
        $fields['targetId'] = $thread['targetId'];
        $this->tryAccess('post.create', $fields);

        $fields['content'] = $this->purifyHtml($fields['content']);
        $fields['userId'] = $user['id'];
        $fields['createdTime'] = time();
        $fields['parentId'] = empty($fields['parentId']) ? 0 : intval($fields['parentId']);

        if ($fields['parentId'] > 0) {
            $parent = $this->getThreadPostDao()->getPost($fields['parentId']);
            if (empty($parent) or ($parent['threadId'] != $fields['threadId'])) {
                throw $this->createServiceException("parentId参数不正确！");
            }

            $this->getThreadPostDao()->wavePost($parent['id'], 'subposts', 1);
        }

        $post = $this->getThreadPostDao()->addPost($fields);

        $this->getThreadDao()->updateThread($thread['id'], array(
            'lastPostUserId' => $post['userId'],
            'lastPostTime' => $post['createdTime'],
        ));

        $this->getThreadDao()->waveThread($thread['id'], 'postNum', +1);

        $this->dispatchEvent('thread.post_create', $post);

        return $post;
    }

    public function deletePost($postId)
    {
        $post = $this->getPost($postId);
        if (empty($post)) {
            throw $this->createServiceException(sprintf('帖子(#%s)不存在，删除失败。', $postId));
        }

        $this->tryAccess('post.delete', $post);

        $thread = $this->getThread($post['threadId']);
        if (!empty($thread)) {
        }

        $totalDeleted = 1;
        if ($post['parentId'] == 0) {
            $totalDeleted += $this->getThreadPostDao()->deletePostsByParentId($post['id']);
        }
        $this->getThreadPostDao()->deletePost($post['id']);

        if ($post['parentId'] > 0) {
            $this->getThreadPostDao()->wavePost($post['parentId'], 'subposts', -1);
        }

        $this->getThreadDao()->waveThread($post['threadId'], 'postNum', $totalDeleted);

        $this->dispatchEvent("thread.post_delete", new ServiceEvent($post, array('deleted' => $totalDeleted)));
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

    public function voteUpPost($id)
    {
        $user = $this->getCurrentUser();
        $post = $this->getThreadPostDao()->getPost($id);


        $this->tryAccess('post.vote', $post);

        $existVote = $this->getThreadVoteDao()->getVoteByThreadIdAndPostIdAndUserId($post['threadId'], $post['id'], $user['id']);
        if ($existVote) {
            return array('status' => 'votedError');
        }

        $fields = array(
            'threadId' => $post['threadId'],
            'postId' => $post['id'],
            'action' => 'up',
            'userId' => $user['id'],
            'createdTime' => time(),
        );

        $vote = $this->getThreadVoteDao()->addVote($fields);

        $this->getThreadPostDao()->wavePost($post['id'], 'ups', 1);

        return array('status' => 'ok');
    }

    public function canAccess($permision, $resource)
    {
        $permisions = array(
            'thread.create' => 'accessThreadCreate',
            'thread.read' => 'accessThreadRead',
            'thread.update' => 'accessThreadUpdate',
            'thread.delete' => 'accessThreadDelete',
            'thread.sticky' => 'accessThreadSticky',
            'thread.nice' => 'accessThreadNice',
            'post.create' => 'accessPostCreate',
            'post.update' => 'accessPostUpdate',
            'post.delete' => 'accessPostDelete',
            'post.vote' => 'accessPostVote',
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
            throw new \InvalidArgumentException("Resource  targetType or targetId argument missing."); 
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

    private function getThreadVoteDao()
    {
        return $this->createDao('Thread.ThreadVoteDao');
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