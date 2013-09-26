<?php

namespace Topxia\Service\Activity\Dao;

interface ThreadPostDao
{

	public function getPost($id);

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit);

	public function getPostCountByThreadId($threadId);

	public function addPost(array $post);

	public function deletePost($id);

	public function deletePostsByThreadId($threadId);
}