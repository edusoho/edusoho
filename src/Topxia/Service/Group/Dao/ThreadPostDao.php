<?php 
namespace Topxia\Service\Group\Dao;

interface ThreadPostDao
{
	public function getPost($id);

    public function searchPostsThreadIds($conditions,$orderBy,$start,$limit);

    public function searchPostsThreadIdsCount($conditions);

	public function searchPosts($conditions,$orderBy,$start,$limit);
	
	public function searchPostsCount($conditions);

	public function updatePost($id,$fields);

	public function addPost($fields);

	public function deletePost($id);

	public function deletePostsByThreadId($threadId);

}