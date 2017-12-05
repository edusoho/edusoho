<?php

namespace Biz\Thread\Service;

interface ThreadService
{
    /**
     * thread.
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

    public function searchThreadCount($conditions);

    public function waveThread($id, $field, $diff);

    /**
     * thread_post.
     */
    public function getPost($id);

    public function createPost($fields);

    public function deletePost($postId);

    public function searchPostsCount($conditions);

    public function searchPosts($conditions, $orderBy, $start, $limit);

    public function setPostAdopted($postId);

    public function cancelPostAdopted($postId);

    public function wavePost($id, $field, $diff);

    /**
     * thread_member.
     */
    public function getMemberByThreadIdAndUserId($threadId, $userId);

    public function createMember($fields);

    public function deleteMember($memberId);

    public function deleteMembersByThreadId($threadId);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchMemberCount($conditions);

    public function countPartakeThreadsByUserIdAndTargetType($userId, $targetType);

    /**
     * thread_vote.
     */
    public function voteUpPost($id);
}
