<?php

namespace Topxia\Service\Course\Dao;

interface ThreadPostDao
{

	public function getPost($id);

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit);

	public function getPostCountByThreadId($threadId);
	
	public function getPostCountByuserIdAndThreadId($userId,$threadId);

	public function findPostsByThreadIdAndIsElite($threadId, $isElite, $start, $limit);

	public function searchThreadPosts($conditions,$orderBy, $start, $limit,$groupBy);

	public function searchThreadPostsCount($conditions,$groupBy);

	public function addPost(array $fields);

	public function updatePost($id, array $fields);

	public function deletePost($id);

	public function deletePostsByThreadId($threadId);

}