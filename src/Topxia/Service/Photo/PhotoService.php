<?php
namespace Topxia\Service\Photo;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface PhotoService
{

	/**
	* Action API
	*/
	public function getPhoto($id);

	public function findPhotoByIds(array $ids);

	public function searchPhotos($conditions, $sort = 'latest', $start, $limit);

	public function searchPhotoCount($conditions);

	public function createPhoto($course);

	public function updatePhoto($id, $fields);

	public function deletePhoto($id);




	/**
	*	Photo File
	*/
	public function getPhotoFile($id);

	public function searchFileCount($conditions);

	public function searchFiles($conditions, $sort = 'latest', $start, $limit);

	public function findFileByIds(array $ids);

	public function findFiles($start, $limit);

	public function findFileCount($conditions);

	public function createPhotoFile($file);

	public function deleteFile($id);

	public function updatePhotoFile($id, $fields);

	
	/**
	 *  photo Comment 
	 */

	public function getComment($id);

	public function findCommentsByFileId($fileId, $sort = 'latest', $start, $limit);

	public function searchThreadCount($conditions);

	public function addComment($thread);

	public function updateComment($id, $fields);

	public function deleteComment($id);

	public function deleteCommentByIds(array $ids);

	

}