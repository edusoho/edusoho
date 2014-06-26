<?php

namespace Topxia\Service\Group;

interface ThreadService
{

    public function getThreadCount($conditions);

    public function getThread($id);
    
    public function addThread($thread);

    public function closeThread($threadId);

    public function openThread($threadId);

    public function searchThreads($conditions,$orderBy,$start, $limit);

    public function postThread($threadContent,$groupId,$memberId,$threadId);

    public function searchPosts($conditions,$orderBy,$start,$limit);

    public function getPost($id);

    public function searchPostsCount($conditions);

    public function deletePost($postId);

    public function deleteThread($threadId);

    public function setElite($threadId);

    public function removeElite($threadId);

    public function setStick($threadId);

    public function removeStick($threadId);



}