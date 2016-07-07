<?php
namespace Topxia\Service\Thread\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\TextHelper;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Thread\ThreadService;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function getThread($threadId)
    {
        return $this->getThreadDao()->getThread($threadId);
    }

    public function searchThreads($conditions, $sort, $start, $limit)
    {
        $orderBys   = $this->filterSort($sort);
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

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array(
                    array('sticky', 'DESC'),
                    array('createdTime', 'DESC')
                );
                break;
            case 'posted':
                $orderBys = array(
                    array('sticky', 'DESC'),
                    array('lastPostTime', 'DESC')
                );
                break;
            case 'createdNotStick':
                $orderBys = array(
                    array('createdTime', 'DESC')
                );
                break;
            case 'postedNotStick':
                $orderBys = array(
                    array('lastPostTime', 'DESC')
                );
                break;
            case 'popular':
                $orderBys = array(
                    array('hitNum', 'DESC')
                );
                break;

            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        return $orderBys;
    }

    protected function prepareThreadSearchConditions($conditions)
    {
        if (empty($conditions['type'])) {
            unset($conditions['type']);
        }

        if (empty($conditions['keyword'])) {
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

        if (empty($conditions['author'])) {
            unset($conditions['author']);
        }

        if (isset($conditions['author'])) {
            $author               = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        if (!empty($conditions['latest'])) {
            if ($conditions['latest'] == 'week') {
                $conditions['GTEcreatedTime'] = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
            }
        }

        return $conditions;
    }

    public function createThread($thread)
    {
        if (empty($thread['title'])) {
            throw $this->createServiceException("标题名称不能为空！");
        }

        if (empty($thread['content'])) {
            throw $this->createServiceException("话题内容不能为空！");
        }

        if (empty($thread['targetId'])) {
            throw $this->createServiceException(' Id不能为空！');
        }

        if (empty($thread['type']) || !in_array($thread['type'], array('discussion', 'question', 'event'))) {
            throw $this->createServiceException(sprintf('Thread type(%s) is error.', $thread['type']));
        }

        $this->tryAccess('thread.create', $thread);

        $event = $this->dispatchEvent('thread.before_create', $thread);

        if ($event->isPropagationStopped()) {
            throw $this->createServiceException('发帖次数过多，请稍候尝试。');
        }

        $thread = ArrayToolkit::parts($thread, array('targetType', 'targetId', 'relationId', 'categoryId', 'title', 'content', 'ats', 'location', 'userId', 'type', 'maxUsers', 'actvityPicture', 'status', 'startTime', 'endTIme'));

        $thread['title']   = $this->sensitiveFilter($thread['title'], $thread['targetType'].'-thread-create');
        $thread['content'] = $this->sensitiveFilter($thread['content'], $thread['targetType'].'-thread-create');
        $thread['title']   = $this->purifyHtml(empty($thread['title']) ? '' : $thread['title']);
        $thread['content'] = $this->purifyHtml(empty($thread['content'])) ? '' : $thread['content'];
        $thread['ats']     = $this->getUserService()->parseAts($thread['content']);

        $user             = $this->getCurrentUser();
        $thread['userId'] = $user['id'];

        if ($thread['type'] == 'event') {
            if ($this->tryAccess('thread.event.create', $thread)) {
                throw $this->createAccessDeniedException('权限不够!');
            }

            if (!empty($thread['location'])) {
                $thread['location'] = $this->sensitiveFilter($thread['location'], $thread['targetType'].'-thread-create');
            }

            $thread['startTime'] = strtotime($thread['startTime']);
            $thread['maxUsers']  = empty($thread['maxUsers']) ? 0 : intval($thread['maxUsers']);
        } else {
            unset($thread['startTime']);
            unset($thread['maxUsers']);
            unset($thread['location']);
        }

        $thread['createdTime']    = time();
        $thread['updateTime']     = time();
        $thread['lastPostUserId'] = $thread['userId'];
        $thread['lastPostTime']   = $thread['createdTime'];
        $thread                   = $this->getThreadDao()->addThread($thread);

        if (!empty($thread['ats'])) {
            foreach ($thread['ats'] as $userId) {
                if ($thread['userId'] == $userId) {
                    continue;
                }

                $this->getNotifiactionService()->notify($userId, 'thread.at', array(
                    'id'      => $thread['id'],
                    'title'   => $thread['title'],
                    'content' => TextHelper::truncate($thread['content'], 50),
                    'user'    => array('id' => $user['id'], 'nickname' => $user['nickname'])
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

        $fields = ArrayToolkit::parts($fields, array('title', 'content', 'startTime', 'maxUsers', 'location', 'actvityPicture'));

        if (empty($fields)) {
            throw $this->createServiceException('参数缺失，更新失败。');
        }

        $fields['content'] = $this->sensitiveFilter($fields['content'], $thread['targetType'].'-thread-update');
        $fields['title']   = $this->sensitiveFilter($fields['title'], $thread['targetType'].'-thread-update');

        //更新thread过滤html
        $fields['content'] = $this->purifyHtml($fields['content']);

        if (!empty($fields['startTime'])) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }

        $this->dispatchEvent('thread.update', new ServiceEvent($thread));

        return $this->getThreadDao()->updateThread($id, $fields);
    }

    protected function sensitiveFilter($str, $type)
    {
        return $this->getSensitiveService()->sensitiveCheck($str, $type);
    }

    public function deleteThread($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $threadId));
        }

        $this->tryAccess('thread.delete', $thread);
        $this->getThreadPostDao()->deletePostsByThreadId($threadId);

        if ($thread['type'] == 'event') {
            $this->deleteMembersByThreadId($thread['id']);
        }

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

        $this->getThreadDao()->updateThread($thread['id'], array('sticky' => 1, 'updateTime' => time()));

        $this->dispatchEvent('thread.sticky', new ServiceEvent($thread, array('sticky' => 'set')));
    }

    public function cancelThreadSticky($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.sticky', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('sticky' => 0, 'updateTime' => time()));
    }

    public function setThreadNice($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.nice', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('nice' => 1, 'updateTime' => time()));

        $this->dispatchEvent('thread.nice', new ServiceEvent($thread, array('nice' => 'set')));
    }

    public function cancelThreadNice($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->tryAccess('thread.nice', $thread);

        $this->getThreadDao()->updateThread($thread['id'], array('nice' => 0, 'updateTime' => time()));

        $this->dispatchEvent('thread.nice', new ServiceEvent($thread, array('nice' => 'cancel')));
    }

    public function setThreadSolved($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('solved' => 1, 'updateTime' => time()));

        // $this->dispatchEvent('thread.solved', new ServiceEvent($thread, array('nice' => 'set')));
    }

    public function cancelThreadSolved($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
        }

        $this->getThreadDao()->updateThread($thread['id'], array('solved' => 0, 'updateTime' => time()));

        // $this->dispatchEvent('thread.solved', new ServiceEvent($thread, array('nice' => 'cancel')));
    }

    public function hitThread($targetId, $threadId)
    {
        $this->getThreadDao()->waveThread($threadId, 'hitNum', +1);
    }

    public function findThreadPosts($targetId, $threadId, $sort, $start, $limit)
    {
        $thread = $this->getThread($targetId, $threadId);

        if (empty($thread)) {
            return array();
        }

        if ($sort == 'best') {
            $orderBy = array('score', 'DESC');
        } elseif ($sort == 'elite') {
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

    public function getPostPostionInArticle($articleId, $postId)
    {
        return $this->getThreadPostDao()->getPostPostionInArticle($articleId, $postId);
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
        $thread = null;

        if (!empty($fields['threadId'])) {
            $thread = $this->getThread($fields['threadId']);

            $fields['targetType'] = $thread['targetType'];
            $fields['targetId']   = $thread['targetId'];
        }

        $this->tryAccess('post.create', $fields);

        $event = $this->dispatchEvent('thread.post.before_create', $fields);

        if ($event->isPropagationStopped()) {
            throw $this->createServiceException('回复次数过多，请稍候尝试。');
        }

        $fields['content'] = $this->sensitiveFilter($fields['content'], $fields['targetType'].'-thread-post-create');

        $fields['content']     = $this->purifyHtml($fields['content']);
        $fields['ats']         = $this->getUserService()->parseAts($fields['content']);
        $user                  = $this->getCurrentUser();
        $fields['userId']      = $user['id'];
        $fields['createdTime'] = time();
        $fields['parentId']    = empty($fields['parentId']) ? 0 : intval($fields['parentId']);

        $parent = null;

        if ($fields['parentId'] > 0) {
            $parent = $this->getThreadPostDao()->getPost($fields['parentId']);

            if (empty($parent)) {
                throw $this->createServiceException("parentId参数不正确！");
            }

            $this->getThreadPostDao()->wavePost($parent['id'], 'subposts', 1);
        }

        $post = $this->getThreadPostDao()->addPost($fields);

        if (!empty($fields['threadId'])) {
            $this->getThreadDao()->updateThread($thread['id'], array(
                'lastPostUserId' => $post['userId'],
                'lastPostTime'   => $post['createdTime']
            ));

            $this->getThreadDao()->waveThread($thread['id'], 'postNum', +1);
        }

        $notifyData = $this->getPostNotifyData($post, $thread, $user);

        if (!empty($post['ats'])) {
            foreach ($post['ats'] as $userId) {
                if ($user['id'] == $userId) {
                    continue;
                }

                $this->getNotifiactionService()->notify($userId, 'thread.post_at', $notifyData);
            }
        }

        //给主贴主人发通知
        $atUserIds = array_values($post['ats']);

        if ($post['parentId'] == 0 && $thread && ($thread['userId'] != $post['userId']) && (!in_array($thread['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($thread['userId'], 'thread.post_create', $notifyData);
        }

//回复的回复的人给该回复的作者发通知

        if ($post['parentId'] > 0 && ($parent['userId'] != $post['userId']) && (!in_array($parent['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($parent['userId'], 'thread.post_create', $notifyData);
        }

        $this->dispatchEvent('thread.post.create', $post);

        return $post;
    }

    protected function getPostNotifyData($post, $thread, $user)
    {
        return array(
            'id'      => $post['id'],
            'post'    => $post,
            'content' => TextHelper::truncate($post['content'], 50),
            'thread'  => empty($thread) ? null : array('id' => $thread['id'], 'title' => $thread['title']),
            'user'    => array('id' => $user['id'], 'nickname' => $user['nickname'])
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

// if (!empty($thread)) {
        // }

        $totalDeleted = 1;

        if ($post['parentId'] == 0) {
            $totalDeleted += $this->getThreadPostDao()->deletePostsByParentId($post['id']);
        }

        $this->getThreadPostDao()->deletePost($post['id']);

        if ($post['parentId'] > 0) {
            $this->getThreadPostDao()->wavePost($post['parentId'], 'subposts', -1);
        }

        $this->getThreadDao()->waveThread($post['threadId'], 'postNum', 0 - $totalDeleted);

        $this->dispatchEvent("thread.post.delete", new ServiceEvent($post, array('deleted' => $totalDeleted)));
    }

    public function searchPostsCount($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);
        $count      = $this->getThreadPostDao()->searchPostsCount($conditions);

        return $count;
    }

    public function searchPosts($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadPostDao()->searchPosts($conditions, $orderBy, $start, $limit);
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
            'threadId'    => $post['threadId'],
            'postId'      => $post['id'],
            'action'      => 'up',
            'userId'      => $user['id'],
            'createdTime' => time()
        );

        $vote = $this->getThreadVoteDao()->addVote($fields);

        $this->getThreadPostDao()->wavePost($post['id'], 'ups', 1);

        return array('status' => 'ok');
    }

    public function setPostAdopted($postId)
    {
        $post = $this->getThreadPostDao()->getPost($postId);

        if (empty($post)) {
            throw $this->createServiceException(sprintf('话题回复(ID: %s)不存在。', $post['id']));
        }

        $this->tryAccess('post.adopted', $post);
        $this->getThreadPostDao()->updatePost($post['id'], array('adopted' => 1));
    }

    public function cancelPostAdopted($postId)
    {
        $post = $this->getThreadPostDao()->getPost($postId);

        if (empty($post)) {
            throw $this->createServiceException(sprintf('话题回复(ID: %s)不存在。', $post['id']));
        }

        $this->tryAccess('post.adopted', $post);

        $this->getThreadPostDao()->updatePost($post['id'], array('adopted' => 0));
    }

    public function canAccess($permision, $resource)
    {
        $permisions = array(
            'thread.create'        => 'accessThreadCreate',
            'thread.read'          => 'accessThreadRead',
            'thread.update'        => 'accessThreadUpdate',
            'thread.delete'        => 'accessThreadDelete',
            'thread.sticky'        => 'accessThreadSticky',
            'thread.nice'          => 'accessThreadNice',
            'thread.solved'        => 'accessThreadSolved',
            'post.create'          => 'accessPostCreate',
            'post.update'          => 'accessPostUpdate',
            'post.delete'          => 'accessPostDelete',
            'post.vote'            => 'accessPostVote',
            'post.adopted'         => 'accessPostAdopted',
            'thread.event.create'  => 'accessEventCreate',
            'thread.member.delete' => 'accessMemberDelete'
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

    public function createMember($fields)
    {
        $member = $this->getThreadMemberDao()->getMemberByThreadIdAndUserId($fields['threadId'], $fields['userId']);

        if (empty($member)) {
            $thread = $this->getThreadDao()->getThread($fields['threadId']);

            if ($thread['maxUsers'] == $thread['memberNum'] && $thread['maxUsers'] != 0) {
                throw $this->createAccessDeniedException('已超过人数限制!');
            }

            $fields['createdTime'] = time();
            $member                = $this->getThreadMemberDao()->addMember($fields);
            $this->getThreadDao()->waveThread($fields['threadId'], 'memberNum', +1);

            return $member;
        } else {
            throw $this->createServiceException('成员已存在!');
        }
    }

    public function deleteMember($memberId)
    {
        $member               = $this->getThreadMemberDao()->getMember($memberId);
        $thread               = $this->getThreadDao()->getThread($member['threadId']);
        $member['targetType'] = $thread['targetType'];
        $member['targetId']   = $thread['targetId'];
        $this->tryAccess('thread.member.delete', $member);

        if (empty($member)) {
            throw $this->createServiceException('成员不存在!');
        }

        $this->getThreadMemberDao()->deleteMember($memberId);
        $this->getThreadDao()->waveThread($member['threadId'], 'memberNum', -1);
    }

    public function deleteMembersByThreadId($threadId)
    {
        if (empty($threadId)) {
            throw $this->createServiceException('参数错误!');
        }

        $thread = $this->getThread($threadId);
        $this->tryAccess('thread.delete', $thread);

        $this->getThreadMemberDao()->deleteMembersByThreadId($threadId);
    }

    public function setUserBadgeTitle($thread, $users)
    {
        //TO DO
        $namespace = ucfirst($thread['targetType']);
        $users     = $this->createService("{$namespace}:{$namespace}.{$namespace}ThreadService")->setUserBadgeTitle($thread['targetId'], $users);

        return $users;
    }

    public function findTeacherIds($thread)
    {
        $namespace = ucfirst($thread['targetType']);
        $teachers  = $this->createService("{$namespace}:{$namespace}.{$namespace}Service")->findClassroomMembersByRole($thread['targetId'], 'teacher', 0, PHP_INT_MAX);

        return ArrayToolkit::column($teachers, 'userId');
    }

    public function findMembersCountByThreadId($threadId)
    {
        return $this->getThreadMemberDao()->findMembersCountByThreadId($threadId);
    }

    public function findMembersByThreadId($threadId, $start, $limit)
    {
        return ArrayToolkit::index($this->getThreadMemberDao()->findMembersByThreadId($threadId, $start, $limit), 'userId');
    }

    public function findMembersByThreadIdAndUserIds($threadId, $userIds)
    {
        return ArrayToolkit::index($this->getThreadMemberDao()->findMembersByThreadIdAndUserIds($threadId, $userIds), 'userId');
    }

    public function getMemberByThreadIdAndUserId($threadId, $userId)
    {
        return $this->getThreadMemberDao()->getMemberByThreadIdAndUserId($threadId, $userId);
    }

    protected function getTargetFirewall($resource)
    {
        if (empty($resource['targetType']) || empty($resource['targetId'])) {
            throw new \InvalidArgumentException("Resource  targetType or targetId argument missing.");
        }

        $class = __NAMESPACE__."\\".ucfirst($resource['targetType']).'ThreadFirewall';

        return new $class();
    }

    protected function getSensitiveService()
    {
        return $this->createService("SensitiveWord:Sensitive.SensitiveService");
    }

    protected function getThreadDao()
    {
        return $this->createDao('Thread.ThreadDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Thread.ThreadPostDao');
    }

    protected function getThreadVoteDao()
    {
        return $this->createDao('Thread.ThreadVoteDao');
    }

    protected function getThreadMemberDao()
    {
        return $this->createDao('Thread.ThreadMemberDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getNotifiactionService()
    {
        return $this->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
