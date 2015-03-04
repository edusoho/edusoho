<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Thread\ThreadService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Util\TextHelper;
use Topxia\Common\ArrayToolkit;

class ThreadServiceImpl extends BaseService implements ThreadService
{

    public function getThread($threadId)
    {
        return $this->getThreadDao()->getThread($threadId);
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
        $thread['ats'] = $this->getUserService()->parseAts($thread['content']);

        if (empty($thread['targetId'])) {
            throw $this->createServiceException(' Id不能为空！');
        }
        if (empty($thread['type']) or !in_array($thread['type'], array('discussion', 'question'))) {
            throw $this->createServiceException(sprintf('Thread type(%s) is error.', $thread['type']));
        }

        $user = $this->getCurrentUser();
        $thread['userId'] = $user['id'];

        $thread['createdTime'] = time();
        $thread['updateTime'] = time();
        $thread['lastPostUserId'] = $thread['userId'];
        $thread['lastPostTime'] = $thread['createdTime'];
        $thread = $this->getThreadDao()->addThread($thread);

        if (!empty($thread['ats'])) {
            foreach ($thread['ats'] as $userId) {
                if ($thread['userId'] == $userId) {
                    continue;
                }
                $this->getNotifiactionService()->notify($userId, 'thread.at', array(
                    'id' => $thread['id'],
                    'title' => $thread['title'],
                    'content' => TextHelper::truncate($thread['content'], 50),
                    'user' => array('id' => $user['id'], 'nickname' => $user['nickname']),
                ));
            }
        }

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

    public function getPost($id)
    {
        return $this->getThreadPostDao()->getPost($id);
    }

    public function getPostPostionInThread($id)
    {
        $post = $this->getPost($id);
        if (empty($post)) {
            return 0;
        }
        $count = $this->getThreadPostDao()->findPostsCountByThreadIdAndParentIdAndIdLessThan($post['threadId'], 0, $post['id']);
        return $count + 1;
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
        $fields['ats'] = $this->getUserService()->parseAts($fields['content']);
        $fields['userId'] = $user['id'];
        $fields['createdTime'] = time();
        $fields['parentId'] = empty($fields['parentId']) ? 0 : intval($fields['parentId']);

        $parent = null;
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

        $notifyData = $this->getPostNotifyData($post, $thread, $user);

        if (!empty($post['ats'])) {
            foreach ($post['ats'] as $userId) {
                if ($user['id'] == $userId) {
                    continue;
                }
                $this->getNotifiactionService()->notify($userId, 'thread.post_at', $notifyData);
            }
        }

        $atUserIds = array_values($post['ats']);
        if ($post['parentId'] == 0 and ($thread['userId'] != $post['userId']) and (!in_array($thread['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($thread['userId'], 'thread.post_create', $notifyData);
        }

        if ($post['parentId'] > 0 and ($parent['userId'] != $post['userId']) and (!in_array($parent['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($parent['userId'], 'thread.post_create', $notifyData);
        }

        $this->dispatchEvent('thread.post_create', $post);

        return $post;
    }

    private function getPostNotifyData($post, $thread, $user)
    {
        return array(
            'id' => $post['id'],
            'content' => TextHelper::truncate($post['content'], 50),
            'thread' => array('id' => $thread['id'], 'title' => $thread['title']),
            'user' => array('id' => $user['id'], 'nickname' => $user['nickname']),
        );
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

        $this->getThreadDao()->waveThread($post['threadId'], 'postNum', 0-$totalDeleted);

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