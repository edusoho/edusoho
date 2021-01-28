<?php

namespace Biz\Thread\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Util\TextHelper;
use Biz\Thread\Dao\ThreadDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Thread\Dao\ThreadPostDao;
use Biz\Thread\Dao\ThreadVoteDao;
use Biz\User\Service\UserService;
use Biz\System\Service\LogService;
use Biz\Thread\Dao\ThreadMemberDao;
use Biz\Thread\Service\ThreadService;
use Codeages\Biz\Framework\Event\Event;
use Biz\User\Service\NotificationService;
use Biz\Sensitive\Service\SensitiveService;
use Biz\Thread\ThreadException;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    /*
     * thread
     */

    public function getThread($threadId)
    {
        return $this->getThreadDao()->get($threadId);
    }

    public function createThread($thread)
    {
        if (empty($thread['title'])) {
            $this->createNewException(ThreadException::TITLE_REQUIRED());
        }

        if (empty($thread['content'])) {
            $this->createNewException(ThreadException::CONTENT_REQUIRED());
        }

        if (empty($thread['targetId'])) {
            $this->createNewException(ThreadException::TARGETID_REQUIRED());
        }

        if (empty($thread['type']) || !in_array($thread['type'], array('discussion', 'question', 'event'))) {
            $this->createNewException(ThreadException::TYPE_INVALID());
        }

        $this->tryAccess('thread.create', $thread);

        $event = $this->dispatchEvent('thread.before_create', $thread);

        if ($event->isPropagationStopped()) {
            $this->createNewException(ThreadException::FORBIDDEN_TIME_LIMIT());
        }

        $thread = ArrayToolkit::parts($thread, array('targetType', 'targetId', 'relationId', 'categoryId', 'title', 'content', 'ats', 'location', 'userId', 'type', 'maxUsers', 'actvityPicture', 'status', 'startTime', 'endTIme'));

        $thread['title'] = $this->sensitiveFilter($thread['title'], $thread['targetType'].'-thread-create');
        $thread['content'] = $this->sensitiveFilter($thread['content'], $thread['targetType'].'-thread-create');
        $thread['title'] = $this->purifyHtml($thread['title']);
        $thread['content'] = $this->purifyHtml($thread['content']);
        $thread['ats'] = $this->getUserService()->parseAts($thread['content']);

        $user = $this->getCurrentUser();
        $thread['userId'] = $user['id'];

        if ('event' == $thread['type']) {
            $this->tryAccess('thread.event.create', $thread);

            if (!empty($thread['location'])) {
                $thread['location'] = $this->sensitiveFilter($thread['location'], $thread['targetType'].'-thread-create');
            }

            $thread['startTime'] = strtotime($thread['startTime']);
            $thread['maxUsers'] = empty($thread['maxUsers']) ? 0 : intval($thread['maxUsers']);
        } else {
            unset($thread['startTime']);
            unset($thread['maxUsers']);
            unset($thread['location']);
        }

        $thread['lastPostUserId'] = $thread['userId'];
        $thread['lastPostTime'] = time();
        $thread = $this->getThreadDao()->create($thread);
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
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.update', $thread);

        $fields = ArrayToolkit::parts($fields, array('title', 'content', 'startTime', 'maxUsers', 'location', 'actvityPicture'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $fields['content'] = $this->sensitiveFilter($fields['content'], $thread['targetType'].'-thread-update');
        $fields['title'] = $this->sensitiveFilter($fields['title'], $thread['targetType'].'-thread-update');

        //更新thread过滤html
        $fields['content'] = $this->purifyHtml($fields['content']);

        if (!empty($fields['startTime'])) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }

        $this->dispatchEvent('thread.update', new Event($thread));

        return $this->getThreadDao()->update($id, $fields);
    }

    public function deleteThread($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.delete', $thread);
        $this->getThreadPostDao()->deletePostsByThreadId($threadId);

        if ('event' == $thread['type']) {
            $this->deleteMembersByThreadId($thread['id']);
        }

        $this->getThreadDao()->delete($threadId);

        $this->dispatchEvent('thread.delete', $thread);

        return true;
    }

    public function setThreadSticky($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.sticky', $thread);

        $fields = array(
            'sticky' => 1,
            'updateTime' => time(),
        );
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);

        $this->dispatchEvent('thread.sticky', new Event($thread, array('sticky' => 'set')));

        return $threadUpdate;
    }

    public function cancelThreadSticky($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.sticky', $thread);

        $fields = array(
            'sticky' => 0,
            'updateTime' => time(),
        );
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);

        $this->dispatchEvent('thread.cancel.sticky', new Event($thread, array('nice' => 'cancel')));

        return $threadUpdate;
    }

    public function setThreadNice($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.nice', $thread);

        $fields = array(
            'nice' => 1,
            'updateTime' => time(),
        );
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);

        $this->dispatchEvent('thread.nice', new Event($thread, array('nice' => 'set')));

        return $threadUpdate;
    }

    public function cancelThreadNice($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.nice', $thread);

        $fields = array(
            'nice' => 0,
            'updateTime' => time(),
        );
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);

        $this->dispatchEvent('thread.cancel.nice', new Event($thread, array('nice' => 'cancel')));

        return $threadUpdate;
    }

    public function setThreadSolved($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $fields = array('solved' => 1, 'updateTime' => time());
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);
        // $this->dispatchEvent('thread.solved', new Event($thread, array('nice' => 'set')));

        return $threadUpdate;
    }

    public function hitThread($targetId, $threadId)
    {
        $this->waveThread($threadId, 'hitNum', +1);
    }

    public function cancelThreadSolved($threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $fields = array('solved' => 0, 'updateTime' => time());
        $threadUpdate = $this->getThreadDao()->update($thread['id'], $fields);

        // $this->dispatchEvent('thread.solved', new Event($thread, array('nice' => 'cancel')));

        return $threadUpdate;
    }

    public function searchThreads($conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function searchThreadCount($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadDao()->count($conditions);
    }

    public function waveThread($id, $field, $diff)
    {
        return $this->getThreadDao()->wave(array($id), array($field => $diff));
    }

    /*
     * thread_post
     */

    public function getPost($id)
    {
        return $this->getThreadPostDao()->get($id);
    }

    public function createPost($fields)
    {
        $thread = null;

        if (!empty($fields['threadId'])) {
            $thread = $this->getThread($fields['threadId']);

            $fields['targetType'] = $thread['targetType'];
            $fields['targetId'] = $thread['targetId'];
        }

        $this->tryAccess('post.create', $fields);

        $event = $this->dispatchEvent('thread.post.before_create', $fields);

        if ($event->isPropagationStopped()) {
            $this->createNewException(ThreadException::FORBIDDEN_TIME_LIMIT());
        }

        $fields['content'] = $this->sensitiveFilter($fields['content'], $fields['targetType'].'-thread-post-create');

        $fields['content'] = $this->purifyHtml($fields['content']);
        $fields['ats'] = $this->getUserService()->parseAts($fields['content']);
        $user = $this->getCurrentUser();
        $fields['userId'] = $user['id'];
        $fields['createdTime'] = time();
        $fields['parentId'] = empty($fields['parentId']) ? 0 : intval($fields['parentId']);

        $parent = null;

        if ($fields['parentId'] > 0) {
            $parent = $this->getPost($fields['parentId']);

            if (empty($parent)) {
                $this->createNewException(ThreadException::PARENTID_INVALID());
            }

            $this->wavePost($parent['id'], 'subposts', 1);
        }

        $post = $this->getThreadPostDao()->create($fields);

        if (!empty($fields['threadId'])) {
            $this->getThreadDao()->update($thread['id'], array(
                'lastPostUserId' => $post['userId'],
                'lastPostTime' => $post['createdTime'],
            ));

            $this->waveThread($thread['id'], 'postNum', +1);
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

        if (0 == $post['parentId'] && $thread && ($thread['userId'] != $post['userId']) && (!in_array($thread['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($thread['userId'], 'thread.post_create', $notifyData);
        }

        //回复的回复的人给该回复的作者        发通知

        if ($post['parentId'] > 0 && ($parent['userId'] != $post['userId']) && (!in_array($parent['userId'], $atUserIds))) {
            $this->getNotifiactionService()->notify($parent['userId'], 'thread.post_create', $notifyData);
        }

        $this->dispatchEvent('thread.post.create', $post);

        return $post;
    }

    public function deletePost($postId)
    {
        $post = $this->getPost($postId);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        $this->tryAccess('post.delete', $post);

        //        $thread = $this->getThread($post['threadId']);

        $totalDeleted = 1;

        if (0 == $post['parentId']) {
            $totalDeleted += $this->getThreadPostDao()->deletePostsByParentId($post['id']);
        }

        $this->getThreadPostDao()->delete($post['id']);

        if ($post['parentId'] > 0) {
            $this->wavePost($post['parentId'], 'subposts', -1);
        }

        $this->waveThread($post['threadId'], 'postNum', 0 - $totalDeleted);

        $this->dispatchEvent('thread.post.delete', new Event($post, array('deleted' => $totalDeleted)));

        return true;
    }

    public function searchPostsCount($conditions)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);
        $count = $this->getThreadPostDao()->count($conditions);

        return $count;
    }

    public function searchPosts($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareThreadSearchConditions($conditions);

        return $this->getThreadPostDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function wavePost($id, $field, $diff)
    {
        return $this->getThreadPostDao()->wave(array($id), array($field => $diff));
    }

    public function setPostAdopted($postId)
    {
        $post = $this->getPost($postId);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        $this->tryAccess('post.adopted', $post);
        $this->getThreadPostDao()->update($post['id'], array('adopted' => 1));
    }

    public function cancelPostAdopted($postId)
    {
        $post = $this->getPost($postId);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        $this->tryAccess('post.adopted', $post);

        $this->getThreadPostDao()->update($post['id'], array('adopted' => 0));
    }

    /*
     * thread_member
     */

    public function getMember($memberId)
    {
        return $this->getThreadMemberDao()->get($memberId);
    }

    public function getMemberByThreadIdAndUserId($threadId, $userId)
    {
        return $this->getThreadMemberDao()->getMemberByThreadIdAndUserId($threadId, $userId);
    }

    public function createMember($fields)
    {
        $member = $this->getThreadMemberDao()->getMemberByThreadIdAndUserId($fields['threadId'], $fields['userId']);

        if (empty($member)) {
            $thread = $this->getThread($fields['threadId']);

            if ($thread['maxUsers'] == $thread['memberNum'] && 0 != $thread['maxUsers']) {
                $this->createNewException(ThreadException::MEMBER_NUMBER_LIMIT());
            }

            $fields['createdTime'] = time();
            $member = $this->getThreadMemberDao()->create($fields);
            $this->waveThread($fields['threadId'], 'memberNum', +1);

            return $member;
        } else {
            $this->createNewException(ThreadException::MEMBER_EXISTED());
        }
    }

    public function deleteMember($memberId)
    {
        $member = $this->getMember($memberId);

        if (empty($member)) {
            $this->createNewException(ThreadException::NOTFOUND_MEMBER());
        }

        $thread = $this->getThread($member['threadId']);
        $member['targetType'] = $thread['targetType'];
        $member['targetId'] = $thread['targetId'];
        $this->tryAccess('thread.member.delete', $member);

        $this->getThreadMemberDao()->delete($memberId);
        $this->waveThread($member['threadId'], 'memberNum', -1);
    }

    public function deleteMembersByThreadId($threadId)
    {
        $thread = $this->getThread($threadId);
        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $this->tryAccess('thread.delete', $thread);

        $this->getThreadMemberDao()->deleteMembersByThreadId($threadId);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchMemberCount($conditions)
    {
        return $this->getThreadMemberDao()->count($conditions);
    }

    public function findThreadIds($conditions)
    {
        $threadIds = $threadIds = $this->getThreadDao()->findThreadIds($conditions);

        return ArrayToolkit::column($threadIds, 'id');
    }

    public function findPostThreadIds($conditions)
    {
        $postThreadIds = $this->getThreadPostDao()->findThreadIds($conditions);

        return ArrayToolkit::column($postThreadIds, 'threadId');
    }

    public function countPartakeThreadsByUserIdAndTargetType($userId, $targetType)
    {
        $threadIds = $this->findThreadIds(array('userId' => $userId, 'targetType' => $targetType));

        $postThreadIds = $this->findPostThreadIds(array('userId' => $userId, 'targetType' => $targetType));

        return count(array_unique(array_merge($threadIds, $postThreadIds)));
    }

    /*
     * thread_vote
     */

    public function voteUpPost($id)
    {
        $user = $this->getCurrentUser();
        $post = $this->getPost($id);

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

        $this->getThreadVoteDao()->create($fields);

        $this->wavePost($post['id'], 'ups', 1);

        return array('status' => 'ok');
    }

    public function tryAccess($permision, $resource)
    {
        if (!$this->canAccess($permision, $resource)) {
            $this->createNewException(ThreadException::ACCESS_DENIED());
        }

        return true;
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
            'thread.solved' => 'accessThreadSolved',
            'post.create' => 'accessPostCreate',
            'post.update' => 'accessPostUpdate',
            'post.delete' => 'accessPostDelete',
            'post.vote' => 'accessPostVote',
            'post.adopted' => 'accessPostAdopted',
            'thread.event.create' => 'accessEventCreate',
            'thread.member.delete' => 'accessMemberDelete',
        );

        if (!array_key_exists($permision, $permisions)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $firewall = $this->getTargetFirewall($resource);

        $method = $permisions[$permision];

        return $firewall->$method($resource);
    }

    protected function getTargetFirewall($resource)
    {
        if (empty($resource['targetType']) || empty($resource['targetId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $this->biz["thread_firewall.{$resource['targetType']}"];
    }

    protected function sensitiveFilter($str, $type)
    {
        return $this->getSensitiveService()->sensitiveCheck($str, $type);
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array('sticky' => 'DESC', 'createdTime' => 'DESC');
                break;
            case 'posted':
                $orderBys = array('sticky' => 'DESC', 'lastPostTime' => 'DESC');
                break;
            case 'createdNotStick':
                $orderBys = array('createdTime' => 'DESC');
                break;
            case 'postedNotStick':
                $orderBys = array('lastPostTime' => 'DESC');
                break;
            case 'popular':
                $orderBys = array('hitNum' => 'DESC');
                break;

            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBys;
    }

    protected function prepareThreadSearchConditions($conditions)
    {
        if (empty($conditions['keyword'])) {
            unset($conditions['keyword']);
            unset($conditions['keywordType']);
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if (!in_array($conditions['keywordType'], array('title', 'content', 'targetId', 'targetTitle'))) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (!empty($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        if (!empty($conditions['latest'])) {
            if ('week' == $conditions['latest']) {
                $conditions['GTEcreatedTime'] = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
            }
        }

        return $conditions;
    }

    protected function getPostNotifyData($post, $thread, $user)
    {
        return array(
            'id' => $post['id'],
            'post' => $post,
            'content' => TextHelper::truncate($post['content'], 50),
            'thread' => empty($thread) ? null : array('id' => $thread['id'], 'title' => $thread['title']),
            'user' => array('id' => $user['id'], 'nickname' => $user['nickname']),
        );
    }

    /**
     * @return ThreadDao
     */
    protected function getThreadDao()
    {
        return $this->createDao('Thread:ThreadDao');
    }

    /**
     * @return ThreadPostDao
     */
    protected function getThreadPostDao()
    {
        return $this->createDao('Thread:ThreadPostDao');
    }

    /**
     * @return ThreadVoteDao
     */
    protected function getThreadVoteDao()
    {
        return $this->createDao('Thread:ThreadVoteDao');
    }

    /**
     * @return ThreadMemberDao
     */
    protected function getThreadMemberDao()
    {
        return $this->createDao('Thread:ThreadMemberDao');
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
