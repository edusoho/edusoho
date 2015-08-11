<?php

namespace Custom\Service\Homework;

/**
 * 作业评分服务接口.
**/
interface HomeworkService
{
	public function createCustomHomework($courseId,$lessonId,$fields);

	/**
	 * 随机获取一份未曾互评的作业答卷.
	 * @param homeworkId, 作业id.
	 * @param userId, 参加互评的学员用户id.
	 * @return 未被userId的作业答卷.
	**/
	public function randomizeHomeworkResultForPairReview($homeworkId,$userId);

}