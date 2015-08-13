<?php

namespace Custom\Service\Homework;

/**
 * 作业评分服务接口.
**/
interface HomeworkService
{
	/**
	 * 创建一份作业
	 * @param $courseId
	 * @param $lessonId
	 * @param $fields
	 * @return mixed
	 */
	public function createHomework($courseId,$lessonId,$fields);

	/**
	 * 更新作业
	 * @param $id
	 * @param $fields
	 * @return mixed
	 */
	public function updateHomework($id, $fields);

	/**
	 * 随机获取一份未曾互评的作业答卷.
	 * @param homeworkId, 作业id.
	 * @param userId, 参加互评的学员用户id.
	 * @return 未被userId的作业答卷.
	**/
	public function randomizeHomeworkResultForPairReview($homeworkId,$userId);

}