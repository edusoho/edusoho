<?php

namespace Biz\Thread\Service;

interface ThreadService
{
    /**
     * thread
     */

    public function getThread($threadId);

    public function createThread($thread);

    public function updateThread($id, $fields);

    public function deleteThread($threadId);

    public function setThreadSticky($threadId);

    public function cancelThreadSticky($threadId);

    public function setThreadNice($threadId);

    public function cancelThreadNice($threadId);

    public function setThreadSolved($threadId);

    public function hitThread($targetId, $threadId);

    public function cancelThreadSolved($threadId);

    public function searchThreads($conditions, $sort, $start, $limit);

    public function countThreads($conditions);

    /**
     * thread_post
     */

    public function getPost($id);

    public function createPost($fields);

    public function deletePost($postId);

    public function getPostPostionInArticle($articleId, $postId);

    public function searchPostsCount($conditions);

    public function searchPosts($conditions, $orderBy, $start, $limit);

    public function voteUpPost($id);

    public function setPostAdopted($postId);

    public function cancelPostAdopted($postId);

    /**
     * thread_member
     */

    public function getMemberByThreadIdAndUserId($threadId, $userId);

    public function createMember($fields);

    public function deleteMember($memberId);

    public function deleteMembersByThreadId($threadId);

    public function findMembersCountByThreadId($threadId);

    public function findMembersByThreadId($threadId, $start, $limit);

    public function findMembersByThreadIdAndUserIds($threadId, $userIds);

}
