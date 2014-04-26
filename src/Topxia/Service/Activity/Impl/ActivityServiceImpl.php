<?php
namespace Topxia\Service\Activity\Impl;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Activity\ActivityService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\DebugToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ActivityServiceImpl extends BaseService implements ActivityService
{

	public function getActivity($id){
			return ActivitySerialize::unserialize($this->getActivityDao()->getActivity($id));
	}


	public function tryAdminActivity($id)
	{
		$activity = $this->getActivityDao()->getActivity($id);
		
		if (empty($activity)) {
			throw $this->createNotFoundException();
		}

		$user = $this->getCurrentUser();
		if (empty($user->id)) {
			throw $this->createAccessDeniedException('未登录用户，无权操作！');
		}

		if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) == 0) {
			throw $this->createAccessDeniedException('您不是管理员，无权操作！');
		}

		return ActivitySerialize::unserialize($activity);
	}




	public function findActivitysByIds(array $ids){
		$activity = ActivitySerialize::unserializes(
            $this->getActivityDao()->findActivitysByIds($ids)
        );
        return ArrayToolkit::index($activity, 'id');
	}


	public function findLastActivitys(){
		$activity = ActivitySerialize::unserializes(
            $this->getActivityDao()->findLastActivitys()
        );
        return ArrayToolkit::index($activity, 'id');
	}
	

	public function findRecommendedActivity(){
		$activity = ActivitySerialize::unserializes(
            $this->getActivityDao()->findRecommendedActivity()
        );
        return ArrayToolkit::index($activity, 'id');
	}


	public function searchActivitys($conditions, $sort = 'latest', $start, $limit){
		
		$conditions = $this->_prepareActivityConditions($conditions);

		if ($sort == 'popular') {
			$orderBy =  array('viewNum', 'DESC');
		} else if ($sort == 'latest'){
			$orderBy = array('startTime', 'DESC');
		} else  {
			$orderBy = array('createdTime', 'DESC');
		}
		return ActivitySerialize::unserializes($this->getActivityDao()->searchActivitys($conditions, $orderBy, $start, $limit));
	}

	public function searchActivityCount($conditions){
		$conditions = $this->_prepareActivityConditions($conditions);
		return $this->getActivityDao()->searchActivityCount($conditions);
	}

	public function createActivity($activity){

		if (!ArrayToolkit::requireds($activity, array('title'))) {
			throw $this->createServiceException('缺少必要字段，创建活动失败！');
		}

		$activity = ArrayToolkit::parts($activity, array('title', 'actType','about', 'categoryId', 'tagsId', 'price', 'startTime', 'endTime', 'city', 'address'));

		$activity['status'] = 'draft';
        $activity['about'] = !empty($activity['about']) ? $this->getHtmlPurifier()->purify($activity['about']) : '';
        $activity['tags'] = !empty($activity['tags']) ? $activity['tags'] : '';
        $activity['address'] = !empty($activity['address']) ? $activity['address'] : '';
        $activity['startTime'] = empty($activity['startTime']) ? 0 : (int) $activity['startTime'];
        $activity['endTime'] = empty($activity['endTime']) ? 0 : (int) $activity['endTime'];
		$activity['userId'] = $this->getCurrentUser()->id;
		$activity['createdTime'] = time();
		$activity['experters'] = array($activity['userId']);
		$activity = $this->getActivityDao()->addActivity(ActivitySerialize::serialize($activity));
		return $this->getActivity($activity['id']);
	}

	public function updateActivity($id, $fields){

		$activity = $this->getActivityDao()->getActivity($id);
		if (empty($activity)) {
			throw $this->createServiceException('活动不存在，更新失败！');
		}



		$fields = $this->_filterActivityFields($fields);

		

		$fields = ActivitySerialize::serialize($fields);

	

		return $this->getActivityDao()->updateActivity($id, $fields);
	}

	public function deleteActivity($id){
		$this->getActivityDao()->deleteActivity($id);
		return true;
	}

	public function addActivityStudentNum($activityid){
		$activity=$this->getActivityDao()->getActivity($activityid);		
		if(empty($activity)){
			throw $this->createServiceException('活动不存在，操作失败！');
		}
		$field['studentNum']=(int)$activity['studentNum']+1;
		return $this->getActivityDao()->updateActivity($activityid,$field);
	}

	public function reduceActivityStudentNum($activityid){
		$activity=$this->getActivityDao()->getActivity($activityid);		
		if(empty($activity)){
			throw $this->createServiceException('活动不存在，操作失败！');
		}
		$field['studentNum']=(int)$activity['studentNum']-1;
		return $this->getActivityDao()->updateActivity($activityid,$field);
	}

	public function publishActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('status' => 'published'));
	}

	public function closeActivity($id){

		$course = $this->getActivityDao()->getActivity($id);
		if(empty($course)) {
			throw $this->createServiceException('活动不存在，关闭失败！');
		}
		return $this->getActivityDao()->updateActivity($id, array('status' => 'closed'));
	}


	public function closeRegistrationActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('expired' => 1));	
	}

	public function openRegistrationActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('expired' => 0));
	}

	public function recommendActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('recommended' => 1,'recommendedTime'=>time()));	
	}

	public function cancelRecommendActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('recommended' => 0,'recommendedTime' => 0));	
	}

	public function defaultActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('expired' => 0));	
	}

	public function endActivity($id){
		return $this->getActivityDao()->updateActivity($id, array('expired' => 1));	
	}



	public function setActivityCourse($courseId, $teachers){
		$this->getActivityDao()->updateActivity($courseId,ActivitySerialize::serialize($teachers));
	}
	public function setActivityTeachers($courseId, $teachers){

		$this->getActivityDao()->updateActivity($courseId,ActivitySerialize::serialize($teachers));
	}

	public function setActivitypictures($courseId, $teachers){
		$this->getActivityDao()->updateActivity($courseId,ActivitySerialize::serialize($teachers));
	}

	private function _prepareActivityConditions($conditions)
	{
		if (isset($conditions['date'])) {
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

	private function _filterActivityFields($fields)
	{

		$fields = ArrayToolkit::parts($fields, array(
			'id', 'title', 'subtitle','actType','status','price','priceMode','onlinePrice','onlinePriceMode','needApproval','payment','income','rating','ratingNum', 'categoryId','tags', 'goals','audiences','outline','about','summary', 'startTime', 'endTime','duration', 'city', 'address','strstartTime','strendTime','form','onlineAddress'
		));


		if (isset($fields['tags'])) {
			$fields['tags'] = $fields['tags'] ? : array();
			array_walk($fields['tags'], function(&$item, $key) {
				$item = (int) $item;
			});
		}

		if (isset($fields['startTime'])) {
			$fields['startTime'] = (int) $fields['startTime'];
		}

		if (isset($fields['rating'])) {
			$fields['rating'] = (int) $fields['rating'];
		}
		

		if (isset($fields['ratingNum'])) {
			$fields['ratingNum'] = (int) $fields['ratingNum'];
		}

		if (isset($fields['endTime'])) {
			$fields['endTime'] = (int) $fields['endTime'];
		}

		return $fields;
	}
	
	public function  extActivitys(array $activitys)
	{
		$extActivitys = array();
        if (empty($activitys)) {
            return $extActivitys;
        }

		foreach ($activitys as $actId => $activity) {

			$users = $this->getUserService()->findUsersByIds($activity['experters']);
			$profiles = $this->getUserService()->findUserProfilesByIds($activity['experters']);

			$userProfiles = array();

			foreach ($users as $userId => $user) {

				$profile =  $profiles[$userId];

				$userProfile = array_merge($user,$profile);

				$userProfiles[$userId]=$userProfile;
			}


			$activity['userProfiles']=$userProfiles;

		    $extActivitys[$actId] = $activity;

		}

		 return $extActivitys;
		
	}


	public function  extActivity(array $activity)
	{
		
        if (empty($activity)) {
            return $activity;
        }

		$users = $this->getUserService()->findUsersByIds($activity['experters']);
		$profiles = $this->getUserService()->findUserProfilesByIds($activity['experters']);

		$userProfiles = array();

		foreach ($users as $userId => $user) {

			$profile =  $profiles[$userId];

			$userProfile = array_merge($user,$profile);

			$userProfiles[$userId]=$userProfile;
		}

		$activity['userProfiles']=$userProfiles;

		return $activity;
		
	}

	public function mixActivitys($activitys, $userId)
	{
		$actIds = ArrayToolkit::column($activitys,'id');

        $joinedActivitys=$this->findMemberByActIds($actIds,$userId);

        $joinActIds = ArrayToolkit::column($joinedActivitys,'activityId');

        $mixActivitys= array();

        foreach ($activitys as $item) {
           
            $item['join']=false;
            
            if (in_array($item['id'], $joinActIds)) {
              $item['join']=true;
            }

            $mixActivitys[$item['id']]= $item;
        }

        return $mixActivitys;

	}


	public function mixActivity($activity, $userId)
	{
		if (empty($activity)) {
            return $activity;
        }
		
		$mixActivity= $activity;

        $condi['userId']=$userId;
        $condi['activityId']=$mixActivity['id'];

        $member = $this->searchMember($condi,0,1);

        $mixActivity['join']=false;

        if(!empty($member) ){
        	$mixActivity['join']=true;

        }

        return $mixActivity;

	}


	// Member API



	public function addMemberByActivity($member){

		if(empty($member['activityId'])){
			throw new Exception("Error Processing Request", 1);
		}
		if(empty($member['userId'])){
			throw new Exception("Error Processing Request", 1);
		}

		$thread['activityId']=$member['activityId'];
		$thread['userId']=$member['userId'];

		$thread['orderId']=$member['orderId'];

		$thread['joinMode']=empty($member['joinMode'])?'':$member['joinMode'];

		$thread['newUser']=empty($member['newUser'])?'0':$member['newUser'];

		$thread['truename']=$member['truename'];
		$thread['createdTime']=time();
		$thread['mobile']=empty($member['mobile'])?'':$member['mobile'];
        $thread['company']=empty($member['company'])?'':$member['company'];
        $thread['job']=empty($member['job'])?'':$member['job'];
        $thread['aboutInfo']=empty($member['aboutInfo'])?'':$member['aboutInfo'];
        
		return $this->getMemberDao()->addMember($thread);
	}

	public function changeActivityPicture($activityId, $filePath, array $options){

		$course = $this->getActivityDao()->getActivity($activityId);
        if (empty($course)) {
            throw $this->createServiceException('课程不存在，图标更新失败！');
        }

        $pathinfo = pathinfo($filePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(470, 300));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 100));
        $largeFileRecord = $this->getFileService()->uploadImgFile('activity', new File($largeFilePath));

        $largeImage->resize(new Box(300, 170));
        $middleFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_middle.{$pathinfo['extension']}";
        $largeImage->save($middleFilePath, array('quality' => 100));
        $middleFileRecord = $this->getFileService()->uploadImgFile('activity', new File($middleFilePath));

        $largeImage->resize(new Box(160, 91));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $largeImage->save($smallFilePath, array('quality' => 100));
        $smallFileRecord = $this->getFileService()->uploadImgFile('activity', new File($smallFilePath));

        return $this->getActivityDao()->updateActivity($activityId, array(
        	'smallPicture' => $smallFileRecord['uri'],
        	'middlePicture' => $middleFileRecord['uri'],
        	'largePicture' => $largeFileRecord['uri'],
    	));
	}

	public function searchMemberCount($conditions)
	{
		return $this->getMemberDao()->searchMemberCount($conditions);
	}

	public function searchMember($conditions, $start, $limit)
	{
		return $this->getMemberDao()->searchMember($conditions, $start, $limit);
	}


    // 某个指定的人参加的活动，一个活动一个人只能报名一次。。。。
	public function findMemberByActIds(array $actIds,$userId)
    {
       
          $members=  $this->getMemberDao()->findMembersByIds($actIds,$userId);
      
          return ArrayToolkit::index($members, 'activityId');
    }



	public function updateActivityMember($id, $fields)
	{
		return $this->getMemberDao()->updateMember($id, $fields);
	}

	public function getActivityMember($activityId, $userId)
	{
		return $this->getMemberDao()->getMemberByActivityIdAndUserId($activityId, $userId);
	}

	public function findActivityStudents($activityId, $start, $limit)
	{
		return $this->getMemberDao()->findMembersByActivityId($activityId, $start, $limit);
	}
	public function findStudentActivitys($userid,$start,$limit){
		return $this->getMemberDao()->findMembersByUserId($userid, $start, $limit);
	}

	public function removeMember($activityId, $userId){
		return $this->getMemberDao()->deleteMemberByActivityIdAndUserId($activityId, $userId);	
	}

	public function getActivityStudentCount($activityId)
	{
		return $this->getMemberDao()->findMemberCountByActivityIdAndRole($activityId);
	}


	private function getActivityDao(){
		return $this->createDao('Activity.ActivityDao');
	}

	private function getMemberDao(){
		return $this->createDao('Activity.MemberDao');
	} 

	private function getFileService()
    {
    	return $this->createService('Content.FileService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }


}

class ActivitySerialize
{

	 //将php对象变成数据库字段。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。
    public static function serialize(array &$activity)
    {
    	if (isset($activity['tags'])) {
    		if (is_array($activity['tags']) and !empty($activity['tags'])) {
    			$activity['tags'] = '|' . implode('|', $activity['tags']) . '|';
    		} else {
    			$activity['tags'] = '';
    		}
    	}
    	
    	if (isset($activity['experters'])) {
    		if (is_array($activity['experters']) and !empty($activity['experters'])) {
    			$activity['experters'] = '|' . implode('|', $activity['experters']) . '|';
    		} else {
    			$activity['experters'] = null;
    		}
    	}

    	if (isset($activity['photoId'])) {
    		if (is_array($activity['photoId']) and !empty($activity['photoId'])) {
    			$activity['photoId'] = '|' . implode('|', $activity['photoId']) . '|';
    		} else {
    			$activity['photoId'] = null;
    		}
    	}

    	if (isset($activity['courseId'])) {
    		if (is_array($activity['courseId']) and !empty($activity['courseId'])) {
    			$activity['courseId'] = '|' . implode('|', $activity['courseId']) . '|';
    		} else {
    			$activity['courseId'] = null;
    		}
    	}

    	if (isset($activity['strstartTime'])) {
    		if (!empty($activity['strstartTime'])) {
    			$activity['startTime'] = strtotime($activity['strstartTime']);
    		} 
    	}
    	unset($activity['strstartTime']);

    	if (isset($activity['strendTime'])) {
    		if (!empty($activity['strendTime'])) {
    			$activity['endTime'] = strtotime($activity['strendTime']);
    		}
    	}
    	unset($activity['strendTime']);


        return $activity;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $activity = null)
    {
    	if (empty($activity)) {
    		return $activity;
    	}

		$activity['tags'] = empty($activity['tags']) ? array() : explode('|', trim($activity['tags'], '|'));


		if(empty($activity['experters'] )) {
			$activity['experters'] = array();
		} else {
			$activity['experters'] = explode('|', trim($activity['experters'], '|'));
		}

		if(empty($activity['photoId'] )) {
			$activity['photoId'] = array();
		} else {
			$activity['photoId'] = explode('|', trim($activity['photoId'], '|'));
		}


		if(empty($activity['courseId'] )) {
			$activity['courseId'] = array();
		} else {
			$activity['courseId'] = explode('|', trim($activity['courseId'], '|'));
		}

		if(empty($activity['startTime'])){
			$activity['startTime']='';
		}else{
			$activity['startTimeNum']=$activity['startTime'];
			$activity['startTime']=date("Y-m-d H:i",$activity['startTime']);
		}
		

		if(empty($activity['endTime'])){
			$activity['endTime']='';
		}else{
			$activity['endTimeNum']=$activity['endTime'];
			$activity['endTime']=date("Y-m-d H:i",$activity['endTime']);
		}

		return $activity;
    }

    public static function unserializes(array $activitys)
    {
    	return array_map(function($activity) {
    		return activitySerialize::unserialize($activity);
    	}, $activitys);
    }
}
