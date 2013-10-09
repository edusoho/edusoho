<?php
namespace Topxia\Service\Photo\Impl;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Photo\PhotoService;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class PhotoServiceImpl extends BaseService implements PhotoService
{

	public function getPhoto($id){
			return PhotoSerialize::unserialize($this->getPhotoDao()->getPhoto($id));
	}

	public function findPhotoByIds(array $ids){
		$activity = PhotoSerialize::unserializes(
            $this->getPhotoDao()->findPhotoByIds($ids)
        );
        return ArrayToolkit::index($activity, 'id');
	}

	public function searchPhotos($conditions, $sort = 'latest', $start, $limit){

		$conditions = $this->_preparePhotoConditions($conditions);
		if ($sort == 'popular') {
			$orderBy =  array('viewNum', 'DESC');
		} else {
			$orderBy = array('createdTime', 'DESC');
		}
		return PhotoSerialize::unserializes($this->getPhotoDao()->searchPhotos($conditions, $orderBy, $start, $limit));
	}

	public function searchPhotoCount($conditions){
		$conditions = $this->_preparePhotoConditions($conditions);
		return $this->getPhotoDao()->searchPhotoCount($conditions);
	}

	public function createPhoto($activity){

		if (!ArrayToolkit::requireds($activity, array('name'))) {
			throw $this->createServiceException('缺少必要字段，创建活动失败！');
		}

		$activity = ArrayToolkit::parts($activity, array('name',  'tagIds',));

        $activity['tagIds'] = !empty($activity['tagIds']) ? $activity['tagIds'] : '';
		$activity['createdTime'] = time();
		$activity = $this->getPhotoDao()->addPhoto(PhotoSerialize::serialize($activity));
		return $this->getPhoto($activity['id']);
	}

	public function updatePhoto($id, $fields){
		$photo = $this->getPhotoDao()->getPhoto($id);
		if (empty($photo)) {
			throw $this->createServiceException('专辑不存在，更新失败！');
		}
		$fields = $this->_filterPhotoFields($fields);
		$fields = PhotoSerialize::serialize($fields);
		return $this->getPhotoDao()->updatePhoto($id, $fields);
	}

	public function deletePhoto($id){
		$this->getPhotoDao()->deletePhoto($id);
		return true;
	}

	private function _preparePhotoConditions($conditions)
	{
		return $conditions;
	}

	private function _filterPhotoFields($fields)
	{

		$fields = ArrayToolkit::parts($fields, array(
			'id', 'name','userId','createdTime'
		));


		if (isset($fields['tagIds'])) {
			$fields['tagIds'] = $fields['tagIds'] ? : array();
			array_walk($fields['tagIds'], function(&$item, $key) { 
				$item = (int) $item;
			});
		}

		return $fields;
	}


	/**
	*	photo file
	*/

	public function getPhotoFile($id){
		return FileSerialize::unserialize($this->getPhotoFileDao()->getFile($id));
	}

	public function searchFileCount($conditions){
		$conditions = $this->_preparePhotoFileConditions($conditions);
		return $this->getPhotoFileDao()->searchFileCount($conditions);
	}

	public function searchFiles($conditions, $sort = 'latest', $start, $limit){
		$conditions = $this->_preparePhotoFileConditions($conditions);
		$orderBy = array('createdTime', 'DESC');
		return FileSerialize::unserializes($this->getPhotoFileDao()->searchFiles($conditions, $orderBy, $start, $limit));
	}

	public function findFileByIds(array $ids){
		$activity = FileSerialize::unserializes(
            $this->getPhotoFileDao()->findFileByIds($ids)
        );
        return ArrayToolkit::index($activity, 'id');
	}

	public function findFiles($start, $limit){

	}

	public function findFileCount($conditions){

	}

	public function createPhotoFile($file){
		
		if (!ArrayToolkit::requireds($file, array('url'))) {
			throw $this->createServiceException('缺少必要字段，创建活动失败！');
		}
        $activity['url'] = !empty($file['url']) ? $file['url'] : '';
        $activity['groupId']= !empty($file['groupId']) ? $file['groupId'] : '';
        $activity['title']= !empty($file['title']) ? $file['title'] : '';
        $activity['content']= !empty($file['content']) ? $file['content'] : '';
        $activity['userId']= $this->getCurrentUser()->id;
		$activity['createdTime'] = time();
		$activity = $this->getPhotoFileDao()->addFile(FileSerialize::serialize($activity));
		return $this->getPhotoFile($activity['id']);
	}

	public function deleteFile($id){
		$this->getPhotoFileDao()->deleteFile($id);
		return true;
	}

	public function updatePhotoFile($id, $fields){
		$photoFile = $this->getPhotoFileDao()->getFile($id);
		if (empty($photoFile)) {
			throw $this->createServiceException('图片不存在，更新失败！');
		}
		$fields = $this->_preparePhotoFileConditions($fields);
		$fields = FileSerialize::serialize($fields);
		return $this->getPhotoFileDao()->updateFile($id, $fields);
	}

	public function addPhotoCommentNum($id){
		$photoFile=$this->getPhotoFile($id);
		$field['commentNum']=$photoFile['commentNum']+1;
		return $this->updatePhotoFile($id,$field);
	}

	private function _preparePhotoFileConditions($conditions)
	{
		if (isset($conditions['createdTime'])) {
			$dates = array(
				'this_week' => array(
					strtotime('Monday'),
					strtotime('Monday next week'),
				),
				'last_week' => array(
					strtotime('Monday last week'),
					strtotime('Monday'),
				),
				'next_week' => array(
					strtotime('Monday next week'),
					strtotime('Monday next week', strtotime('Monday next week')),
				),
				'this_month' => array(
					strtotime('first day of this month midnight'), 
					strtotime('first day of next month midnight'),
				),
				'last_month' => array(
					strtotime('first day of last month midnight'),
					strtotime('first day of this month midnight'),
				),
				'next_month' => array(
					strtotime('first day of next month midnight'),
					strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
				),
			);

			if (array_key_exists($conditions['date'], $dates)) {
				$conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
				$conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
				unset($conditions['date']);
			}
		}

		return $conditions;
	}

	private function _filterPhotoFileFields($fields)
	{

		$fields = ArrayToolkit::parts($fields, array(
			'id','groupId', 'userId','url','title','content','createdTime'
		));


		if (isset($fields['tagIds'])) {
			$fields['tagIds'] = $fields['tagIds'] ? : array();
			array_walk($fields['tagIds'], function(&$item, $key) { 
				$item = (int) $item;
			});
		}

		return $fields;
	}





	/**
	*	photo commit serivce
	*/

	public function getComment($id){
		return CommentSerialize::unserialize($this->getPhotoCommentDao()->getComment($id));
	}

	public function findCommentsByFileId($fileId, $sort = 'latest', $start, $limit){
		$conditions = $this->_preparePhotoCommentConditions($fileId);
		$orderBy = array('createdTime', 'DESC');
		return CommentSerialize::unserializes($this->getPhotoCommentDao()->findCommentsByFileId($conditions, $orderBy, $start, $limit));
	}

	public function searchThreadCount($conditions){
		$conditions = $this->_preparePhotoCommentConditions($conditions);
		return $this->getPhotoCommentDao()->searchCommentCount($conditions);
	}

	public function addComment($file){
		if (!ArrayToolkit::requireds($file, array('imgId'))) {
			throw $this->createServiceException('缺少必要字段，创建活动失败！');
		}
		$feild['imgId']= !empty($file['imgId']) ? $file['imgId'] : '';
        $feild['content']= !empty($file['content']) ? $file['content'] : '';
        $feild['userId']= $this->getCurrentUser()->id;
		$feild['createdTime'] = time();
		$feild = $this->getPhotoCommentDao()->addComment(CommentSerialize::serialize($feild));
		$this->addPhotoCommentNum($feild['imgId']);
		return $this->getComment($feild['id']);
	}

	public function updateComment($id, $fields){

	}

	public function deleteComment($id){
		$this->getPhotoCommentDao()->deleteComment($id);
		return true;
	}

	public function deleteCommentByIds(array $ids){
		$this->getPhotoCommentDao()->deleteCommentByIds($id);
		return true;
	}


	private function _preparePhotoCommentConditions($conditions)
	{
		if (isset($conditions['createdTime'])) {
			$dates = array(
				'this_week' => array(
					strtotime('Monday'),
					strtotime('Monday next week'),
				),
				'last_week' => array(
					strtotime('Monday last week'),
					strtotime('Monday'),
				),
				'next_week' => array(
					strtotime('Monday next week'),
					strtotime('Monday next week', strtotime('Monday next week')),
				),
				'this_month' => array(
					strtotime('first day of this month midnight'), 
					strtotime('first day of next month midnight'),
				),
				'last_month' => array(
					strtotime('first day of last month midnight'),
					strtotime('first day of this month midnight'),
				),
				'next_month' => array(
					strtotime('first day of next month midnight'),
					strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
				),
			);

			if (array_key_exists($conditions['date'], $dates)) {
				$conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
				$conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
				unset($conditions['date']);
			}
		}

		return $conditions;
	}

	private function _filterPhotoCommentFields($fields)
	{

		$fields = ArrayToolkit::parts($fields, array(
			'id','imgId','userId','content','createdTime'
		));

		if (isset($fields['tagIds'])) {
			$fields['tagIds'] = $fields['tagIds'] ? : array();
			array_walk($fields['tagIds'], function(&$item, $key) { 
				$item = (int) $item;
			});
		}

		return $fields;
	}


	private function getPhotoDao(){
		return $this->createDao('Photo.PhotoDao');
	}

	private function getPhotoFileDao(){
		return $this->createDao('Photo.PhotoFileDao');
	} 

	private function getPhotoCommentDao(){
		return $this->createDao('Photo.PhotoCommentDao');		
	}

	private function getFileService()
    {
    	return $this->createService('Content.FileService');
    }


}

class PhotoSerialize
{
    public static function serialize(array &$activity)
    {
    	if (isset($activity['tagIds'])) {
    		if (is_array($activity['tagIds']) and !empty($activity['tagIds'])) {
    			$activity['tagIds'] = '|' . implode('|', $activity['tagIds']) . '|';
    		} else {
    			$activity['tagIds'] = '';
    		}
    	}
    	
        return $activity;
    }

    public static function unserialize(array $activity = null)
    {
    	if (empty($activity)) {
    		return $activity;
    	}
    	
		$activity['tagIds'] = empty($activity['tagIds']) ? array() : explode('|', trim($activity['tagIds'], '|'));


		return $activity;
    }

    public static function unserializes(array $activitys)
    {
    	return array_map(function($activity) {
    		return PhotoSerialize::unserialize($activity); 
    	}, $activitys);
    }
}


class FileSerialize
{
    public static function serialize(array &$activity)
    {



        return $activity;
    }

    public static function unserialize(array $activity = null)
    {
    	if (empty($activity)) {
    		return $activity;
    	}

		
    	$activity['createdTime']=empty($activity['createdTime'])?'':date('Y-m-d',$activity['createdTime']);

		return $activity;
    }

    public static function unserializes(array $activitys)
    {
    	return array_map(function($activity) {
    		return FileSerialize::unserialize($activity);
    	}, $activitys);
    }
}

class CommentSerialize
{
    public static function serialize(array &$activity)
    {



        return $activity;
    }

    public static function unserialize(array $activity = null)
    {
    	if (empty($activity)) {
    		return $activity;
    	}

    	// $activity['createdTime']=empty($activity['createdTime'])?'':date('Y-m-d',$activity['createdTime']);

		return $activity;
    }

    public static function unserializes(array $activitys)
    {
    	return array_map(function($activity) {
    		return CommentSerialize::unserialize($activity);
    	}, $activitys);
    }
}
