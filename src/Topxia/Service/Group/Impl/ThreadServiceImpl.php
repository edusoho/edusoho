<?php

namespace Topxia\Service\Group\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Group\ThreadService;

class ThreadServiceImpl extends BaseService implements ThreadService
{
    public function getThread($id)
    {
        return $this->getThreadDao()->getThread($id);
    }

    public function isCollected($userId, $threadId)
    {
        $thread = $this->getThreadCollectDao()->getThreadByUserIdAndThreadId($userId, $threadId);

        if (empty($thread)) {
            return false;
        } else {
            return true;
        }
    }

    public function getThreadsByIds($ids)
    {
        $threads = $this->getThreadDao()->getThreadsByIds($ids);
        return ArrayToolkit::index($threads, 'id');
    }

    public function threadCollect($userId, $threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException('话题不存在，收藏失败！');
        }

        if ($userId == $thread['userId']) {
            throw $this->createServiceException('不能收藏自己的话题！');
        }

        $collectThread = $this->getThreadCollectDao()->getThreadByUserIdAndThreadId($userId, $threadId);

        if (!empty($collectThread)) {
            throw $this->createServiceException('不允许重复收藏!');
        }

        $this->dispatchEvent('group.thread.collect', new ServiceEvent($thread));

        return $this->getThreadCollectDao()->addThreadCollect(array(
            "userId"      => $userId,
            "threadId"    => $threadId,
            "createdTime" => time()));
    }

    public function searchGoods($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadGoodsDao()->searchGoods($conditions, $orderBy, $start, $limit);
    }

    public function unThreadCollect($userId, $threadId)
    {
        $thread = $this->getThread($threadId);

        if (empty($thread)) {
            throw $this->createServiceException('话题不存在，取消收藏失败！');
        }

        $collectThread = $this->getThreadCollectDao()->getThreadByUserIdAndThreadId($userId, $threadId);

        if (empty($collectThread)) {
            throw $this->createServiceException('不存在此收藏关系，取消收藏失败！');
        }

        return $this->getThreadCollectDao()->deleteThreadCollectByUserIdAndThreadId($userId, $threadId);
    }

    public function searchThreadCollectCount($conditions)
    {
        return $this->getThreadCollectDao()->searchThreadCollectCount($conditions);
    }

    public function searchThreadsCount($conditions)
    {
        $count = $this->getThreadDao()->searchThreadsCount($conditions);
        return $count;
    }

    public function searchPostsThreadIds($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadPostDao()->searchPostsThreadIds($conditions, $orderBy, $start, $limit);
    }

    public function searchThreadCollects($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadCollectDao()->searchThreadCollects($conditions, $orderBy, $start, $limit);
    }

    public function searchPostsThreadIdsCount($conditions)
    {
        return $this->getThreadPostDao()->searchPostsThreadIdsCount($conditions);
    }

    public function getTradeByUserIdAndGoodsId($userId, $goodsId)
    {
        return $this->getThreadTradeDao()->getTradeByUserIdAndGoodsId($userId, $goodsId);
    }

    public function getPost($id)
    {
        return $this->getThreadPostDao()->getPost($id);
    }

    protected function sensitiveFilter($str, $type)
    {
        return $this->getSensitiveService()->sensitiveCheck($str, $type);
    }

    public function addThread($thread)
    {
        if (empty($thread['title'])) {
            throw $this->createServiceException("标题名称不能为空！");
        }

        if (empty($thread['content'])) {
            throw $this->createServiceException("话题内容不能为空！");
        }

        $event = $this->dispatchEvent('group.thread.before_create', $thread);

        if ($event->isPropagationStopped()) {
            throw $this->createServiceException('发帖次数过多，请稍候尝试。');
        }

        $thread['title']   = $this->sensitiveFilter($thread['title'], 'group-thread-create');
        $thread['content'] = $this->sensitiveFilter($thread['content'], 'group-thread-create');

        $thread['title']   = $this->purifyHtml(empty($thread['title']) ? '' : $thread['title']);
        $thread['content'] = $this->purifyHtml(empty($thread['content']) ? '' : $thread['content']);

        if (empty($thread['groupId'])) {
            throw $this->createServiceException("小组Id不能为空！");
        }

        if (empty($thread['userId'])) {
            throw $this->createServiceException("用户ID不能为空！");
        }

        $thread['createdTime'] = time();
        $thread                = $this->getThreadDao()->addThread($thread);

        $this->getGroupService()->waveGroup($thread['groupId'], 'threadNum', +1);

        $this->getGroupService()->waveMember($thread['groupId'], $thread['userId'], 'threadNum', +1);

        $this->hideThings($thread['content'], $thread['id']);
        $this->dispatchEvent('group.thread.create', $thread);
        $this->getLogService()->info('group', 'create_thread', "新增话题 {$thread['title']}({$thread['id']})");
        return $thread;
    }

    public function deleteGoods($id)
    {
        $this->getThreadGoodsDao()->deleteGoods($id);

        return true;
    }

    public function addAttach($files, $threadId)
    {
        $user = $this->getCurrentUser();

        for ($i = 0; $i < count($files['id']); $i++) {
            $file = $this->getFileService()->getFile($files['id'][$i]);

            if ($file['userId'] != $user->id) {
                continue;
            }

            $hide = $this->getThreadGoodsDao()->searchGoods(array('threadId' => $threadId, 'fileId' => $files['id'][$i]), array('createdTime', 'desc'), 0, 1);

            $files['title'][$i] = $this->subTxt($files['title'][$i]);

            $attach = array(
                'title'       => $files['title'][$i],
                'description' => $files['description'][$i],
                'type'        => 'attachment',
                'userId'      => $user->id,
                'threadId'    => $threadId,
                'coin'        => $files['coin'][$i],
                'fileId'      => $files['id'][$i],
                'createdTime' => time()
            );

            if ($hide) {
                $this->getThreadGoodsDao()->updateGoods($hide[0]['id'], $attach);
                continue;
            }

            $this->getThreadGoodsDao()->addGoods($attach);
        }
    }

    public function addPostAttach($files, $threadId, $postId)
    {
        $user = $this->getCurrentUser();

        for ($i = 0; $i < count($files['id']); $i++) {
            $file = $this->getFileService()->getFile($files['id'][$i]);

            if ($file['userId'] != $user->id) {
                continue;
            }

            $files['title'][$i] = $this->subTxt($files['title'][$i]);

            $attach = array(
                'title'       => $files['title'][$i],
                'description' => $files['description'][$i],
                'type'        => 'postAttachment',
                'userId'      => $user->id,
                'threadId'    => $threadId,
                'coin'        => $files['coin'][$i],
                'fileId'      => $files['id'][$i],
                'postId'      => $postId,
                'createdTime' => time()
            );

            $this->getThreadGoodsDao()->addGoods($attach);
        }
    }

    public function waveGoodsHitNum($goodsId)
    {
        return $this->getThreadGoodsDao()->waveGoodsHitNum($goodsId);
    }

    protected function hideThings($content, $id)
    {
        $content = str_replace("#", "<!--></>", $content);
        $content = str_replace("[hide=coin", "#[hide=coin", $content);

        $user = $this->getCurrentUser();
        $data = explode('[/hide]', $content);

        foreach ($data as $key => $value) {
            $value = " ".$value;
            sscanf($value, "%[^#]#[hide=coin%[^]]]%[^$$]", $content, $coin, $title);

            if (!is_numeric($coin)) {
                $coin = 0;
            }

            if ($coin >= 0 && $title != "") {
                $hide = array(
                    'title'       => $title,
                    'type'        => 'content',
                    'threadId'    => $id,
                    'coin'        => $coin,
                    'userId'      => $user->id,
                    'createdTime' => time());
                $this->getThreadGoodsDao()->addGoods($hide);
            }

            unset($coin);
            unset($title);
        }
    }

    protected function subTxt($string, $length = 10)
    {
        $string = explode(".", $string);

        $text = $this->pureString($string);

        $length = (int) $length;

        if (($length > 0) && (mb_strlen($text, 'utf-8') > $length)) {
            $text = mb_substr($text, 0, $length, 'UTF-8');
        }

        return $text.".".$string[count($string) - 1];
    }

    protected function pureString($string)
    {
        $text = $string[0];
        $text = strip_tags($text);

        $text = str_replace(array("\n", "\r", "\t"), '', $text);
        $text = str_replace('&nbsp;', ' ', $text);
        return trim($text);
    }

    public function getGoods($id)
    {
        return $this->getThreadGoodsDao()->getGoods($id);
    }

    public function sumGoodsCoinsByThreadId($id)
    {
        $condition = array('threadId' => $id, 'type' => "content");
        return $this->getThreadGoodsDao()->sumGoodsCoins($condition);
    }

    public function waveHitNum($threadId)
    {
        $this->getThreadDao()->waveThread($threadId, 'hitNum', +1);
    }

    public function addTrade($fields)
    {
        if (empty($fields['userId'])) {
            throw $this->createServiceException("用户ID不能为空!");
        }

        return $this->getThreadTradeDao()->addTrade($fields);
    }

    public function updateThread($id, $fields)
    {
        if (empty($fields['title'])) {
            throw $this->createServiceException("标题名称不能为空！");
        }

        if (empty($fields['content'])) {
            throw $this->createServiceException("话题内容不能为空！");
        }

        $fields['title']   = $this->sensitiveFilter($fields['title'], 'group-thread-update');
        $fields['content'] = $this->sensitiveFilter($fields['content'], 'group-thread-update');

        $this->getThreadGoodsDao()->deleteGoodsByThreadId($id, 'content');
        $this->hideThings($fields['content'], $id);

        $fields['title']   = $this->purifyHtml($fields['title']);
        $fields['content'] = $this->purifyHtml($fields['content']);

        $thread = $this->getThreadDao()->updateThread($id, $fields);
        $this->dispatchEvent('group.thread.update', $thread);
        return $thread;
    }

    public function closeThread($threadId)
    {
        $thread = $this->getThreadDao()->updateThread($threadId, array('status' => 'close'));
        $this->dispatchEvent('group.thread.close', $thread);
        $this->getLogService()->info('group', 'close_thread', "关闭话题 {$thread['title']}({$thread['id']})");
    }

    public function openThread($threadId)
    {
        $thread = $this->getThreadDao()->updateThread($threadId, array('status' => 'open'));
        $this->dispatchEvent('group.thread.open', $thread);
        $this->getLogService()->info('group', 'open_thread', "开启话题 {$thread['title']}({$thread['id']})");
    }

    public function searchThreads($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadDao()->searchThreads($conditions, $orderBy, $start, $limit);
    }

    public function postThread($threadContent, $groupId, $memberId, $threadId, $postId = 0)
    {
        if (empty($threadContent['content'])) {
            throw $this->createServiceException("回复内容不能为空！");
        }

        $event = $this->dispatchEvent('group.thread.post.before_create', $threadContent);

        if ($event->isPropagationStopped()) {
            throw $this->createServiceException('发帖次数过多，请稍候尝试。');
        }

        $threadContent['content']     = $this->sensitiveFilter($threadContent['content'], 'group-thread-post-create');
        $threadContent['content']     = $this->purifyHtml($threadContent['content']);
        $threadContent['userId']      = $memberId;
        $threadContent['createdTime'] = time();
        $threadContent['threadId']    = $threadId;
        $threadContent['postId']      = $postId;
        $post                         = $this->getThreadPostDao()->addPost($threadContent);
        $this->getThreadDao()->updateThread($threadId, array('lastPostMemberId' => $memberId, 'lastPostTime' => time()));
        $this->getGroupService()->waveGroup($groupId, 'postNum', +1);
        $this->getGroupService()->waveMember($groupId, $memberId, 'postNum', +1);

        if ($postId == 0) {
            $this->waveThread($threadId, 'postNum', +1);
        }

        $thread = $this->getThread($threadId);

        $this->dispatchEvent('group.thread.post.create', $post);

        return $post;
    }

    public function searchPosts($conditions, $orderBy, $start, $limit)
    {
        return $this->getThreadPostDao()->searchPosts($conditions, $orderBy, $start, $limit);
    }

    public function searchPostsCount($conditions)
    {
        return $this->getThreadPostDao()->searchPostsCount($conditions);
    }

    public function setElite($threadId)
    {
        $this->getThreadDao()->updateThread($threadId, array('isElite' => 1));
    }

    public function removeElite($threadId)
    {
        $this->getThreadDao()->updateThread($threadId, array('isElite' => 0));
    }

    public function setStick($threadId)
    {
        $this->getThreadDao()->updateThread($threadId, array('isStick' => 1));
    }

    public function removeStick($threadId)
    {
        $this->getThreadDao()->updateThread($threadId, array('isStick' => 0));
    }

    public function deleteThread($threadId)
    {
        $thread = $this->getThreadDao()->getThread($threadId);
        $this->deletePostsByThreadId($threadId);
        $this->getThreadDao()->deleteThread($threadId);

        $this->getGroupService()->waveGroup($thread['groupId'], 'threadNum', -1);

        $this->getGroupService()->waveMember($thread['groupId'], $threadId, 'threadNum', -1);
        $this->dispatchEvent('group.thread.delete', $thread);
        $this->getLogService()->info('group', 'delete_thread', "删除话题 {$thread['title']}({$thread['id']})");
    }

    public function updatePost($id, $fields)
    {
        if (!empty($fields['content'])) {
            $fields['content'] = $this->sensitiveFilter($fields['content'], 'group-thread-post-update');
            $fields['content'] = $this->purifyHtml($fields['content']);
        }

        $post = $this->getThreadPostDao()->updatePost($id, $fields);
        // $this->dispatchEvent('group.thread.post.update', $post);
        return $post;
    }

    public function deletePost($postId)
    {
        $post     = $this->getThreadPostDao()->getPost($postId);
        $threadId = $post['threadId'];
        $thread   = $this->getThreadDao()->getThread($threadId);

        $this->getThreadPostDao()->deletePost($postId);

        $this->getGroupService()->waveGroup($thread['groupId'], 'postNum', -1);

        $this->getGroupService()->waveMember($thread['groupId'], $threadId, 'postNum', -1);

        $this->waveThread($threadId, 'postNum', -1);

        $this->dispatchEvent('group.thread.post.delete', $post);
    }

    public function deletePostsByThreadId($threadId)
    {
        $thread    = $this->getThreadDao()->getThread($threadId);
        $postCount = $this->getThreadPostDao()->searchPostsCount(array('threadId' => $threadId));

        $this->getGroupService()->waveGroup($thread['groupId'], 'postNum', -$postCount);

        $this->getGroupService()->waveMember($thread['groupId'], $threadId, 'postNum', -$postCount);

        $this->getThreadPostDao()->deletePostsByThreadId($threadId);
    }

    public function getTrade($id)
    {
        return $this->getThreadTradeDao()->getTrade($id);
    }

    protected function waveThread($id, $field, $diff)
    {
        return $this->getThreadDao()->waveThread($id, $field, $diff);
    }

    public function getTradeByUserIdAndThreadId($userId, $threadId)
    {
        return $this->getThreadTradeDao()->getTradeByUserIdAndThreadId($userId, $threadId);
    }

    protected function getThreadTradeDao()
    {
        return $this->createDao('Group.ThreadTradeDao');
    }

    protected function getThreadGoodsDao()
    {
        return $this->createDao('Group.ThreadGoodsDao');
    }

    protected function getThreadDao()
    {
        return $this->createDao('Group.ThreadDao');
    }

    protected function getGroupService()
    {
        return $this->createService('Group.GroupService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Group.ThreadPostDao');
    }

    protected function getThreadCollectDao()
    {
        return $this->createDao('Group.ThreadCollectDao');
    }

    protected function getSensitiveService()
    {
        return $this->createService("SensitiveWord:Sensitive.SensitiveService");
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
