<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadPostDao
{

	public function getPost($id);

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit);

	public function getPostCountByThreadId($threadId);
	
	public function findPostsByParentId($parentId, $start, $limit);

	public function findPostsCountByParentId($parentId);

	public function findPostsCountByThreadIdAndParentIdAndIdLessThan($threadId, $parentId, $id);

	public function searchPostsCount($conditions);

	public function searchPosts($conditions, $orderBy, $start, $limit);

	public function addPost($fields);

	public function updatePost($id, array $fields);

	public function wavePost($id, $field, $diff);

	public function deletePost($id);

	public function deletePostsByThreadId($threadId);

	public function deletePostsByParentId($parentId);
}