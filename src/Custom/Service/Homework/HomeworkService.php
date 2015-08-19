<?php

namespace Custom\Service\Homework;

/**
 * 作业评分服务接口.
 **/
interface HomeworkService
{

    /**
     * 根据id获取作业.
     * @param id , 作业id.
     * @throws ServiceException 当id为空或者未能找到指定id的作业.
     **/
    public function loadHomework($id);

    /**
     * 获得某个课程中的所有作业
     * @param $courseId
     * @return mixed
     */
    public function getHomeworksByCourseId($courseId);

    /**
     * 创建一份作业.
     * @param $courseId
     * @param $lessonId
     * @param $fields
     * @return mixed
     */
    public function createHomework($courseId, $lessonId, $fields);

    /**
     * 更新作业
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function updateHomework($id, $fields);

    /**
     * 提交作业
     * @param $id
     * @param $homework_result
     * @return mixed
     */
    public function submitHomework($id, $homework_result);

    /**
     * 根据courseId和userIdeas查找homework_result
     * @param $courseId
     * @param $userId
     * @return mixed
     */
    public function getResultByCourseIdAndUserId($courseId, $userId);

    /**
     * 根据userId查找homework_item_result
     * @param $userId
     * @return mixed
     */
    public function findItemResultsbyUserId($userId);

    /**
     * 随机获取一份未曾互评的作业答卷.
     * @param homeworkId , 作业id.
     * @param userId , 参加互评的学员用户id.
     * @return 未被userId的作业答卷.
     **/
    public function randomizeHomeworkResultForPairReview($homeworkId, $userId);

    /**
     * 根据id获取一份作业答卷.
     * @param homeworkResultId
     * @return 作业答卷.
     **/
    public function loadHomeworkResult($homeworkResultId);

    /**
     * 更新一个作业答卷.
     * @param homeworkResultId作业答卷id .
     * @param fields 更新字段.
     * @return 更新后的作业答卷.
     **/
    public function updateHomeworkResult($homeworkResultId, array $fields);

    /**
     * 为老师创建一个作业点评.
     * @param homeworkResultId 作业答卷id.
     * @param userId,老师id.
     * @param fields 更新字段.
     * 
    **/
    public function createHomeworkReview($homeworkResultId,$userId, array $fields);

    /**
     * 创建一个作业互评.
     * @param homeworkResultId 作业答卷id.
     * @param userId 同学id.
     * @param fields 互评数据.
     * @return 保存的保存后的互评数据.
     **/
    public function createHomeworkPairReview($homeworkResultId,$userId, array $fields);


    /**
     * 根据id获取作业答题.
     * @param id 作业答题id.
     * @return 作业答题.
    **/
    public function loadHomeworkResultItem($id);
}