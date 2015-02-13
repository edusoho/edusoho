<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadPostDao
{

	public function getPost($id);

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit);

	public function getPostCountByThreadId($threadId);
	
	public function getPostCountByuserIdAndThreadId($userId,$threadId);

	public function findPostsByThreadIdAndIsElite($threadId, $isElite, $start, $limit);

	public function findPostsByParentId($parentId, $start, $limit);

	public function findPostsCountByParentId($parentId);

	public function searchPostsCount($conditions);

	public function searchPosts($conditions,$orderBy,$start,$limit);

	public function addPost($fields);

	public function updatePost($id, array $fields);

	public function deletePost($id);

	public function deletePostsByThreadId($threadId);
}