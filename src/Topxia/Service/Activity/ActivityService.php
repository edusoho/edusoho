<?php
namespace Topxia\Service\Activity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ActivityService
{

	/**
	* Action API
	*/
	public function getActivity($id);

	public function findActivitysByIds(array $ids);

	public function searchActivitys($conditions, $sort = 'latest', $start, $limit);

	public function searchActivityCount($conditions);

	public function createActivity($course);

	public function updateActivity($id, $fields);

	public function deleteActivity($id);

	public function addActivityStudentNum($activityid);

	public function reduceActivityStudentNum($activityid);

	public function setActivityTeachers($courseId, $teachers);

	public function setActivitypictures($courseId, $teachers);

	public function setActivityCourse($courseId, $teachers);


	public function publishActivity($id);

	public function closeActivity($id);

	public function endActivity($id);

	public function defaultActivity($id);

	public function changeActivityPicture ($courseId, $filePath, array $options);

	/**
	* Member API
	*/
	public function addMemberByActivity($member);

	public function removeMember($activityId, $userId);

	public function searchMember($conditions, $start, $limit);

	public function searchMemberCount($conditions);

	public function getActivityMember($courseId, $userId);

	public function updateActivityMember($id, $fields);

	public function findActivityStudents($courseId, $start, $limit);

	public function findStudentActivitys($userid,$start,$limit);

	public function getActivityStudentCount($courseId);





}