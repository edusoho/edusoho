<?php

namespace Custom\Service\Homework;

/**
 * 作业评分服务接口.
**/
interface HomeworkService
{
    public function createCustomHomework($courseId,$lessonId,$fields);

    /**
     * 根据id获取一份作业答卷.
     * @param homeworkResultId
     * @return 作业答卷.
    **/
    public function loadHomeworkResult($homeworkResultId);

    /**
     * 更新一个作业答卷.
     * @param homeworkResultId作业答卷id.
     * @param fields 更新字段.
     * @return 更新后的作业答卷.
    **/
    public function updateHomeworkResult($homeworkResultId, array $fields);
    /**
     * 创建一个作业互评.
     * @param homeworkResultId 作业答卷id.
     * @param fields 互评数据.
     * @return 保存的保存后的互评数据.
    **/
    public function createHomeworkPairReview($homeworkResultId, array $fields);

    /**
     * 随机获取一份未曾互评的作业答卷.
     * @param homeworkId, 作业id.
     * @param userId, 参加互评的学员用户id.
     * @return 未被userId互评的作业答卷.
    **/
    public function randomizeHomeworkResultForPairReview($homeworkId,$userId);

}