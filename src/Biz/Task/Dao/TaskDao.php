<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface TaskDao extends AdvancedDaoInterface
{
    public function deleteByCategoryId($categoryId);

    public function deleteByCourseId($courseId);

    public function findByCourseId($courseId);

    public function findByCourseIds($courseIds);

    public function findByActivityIds($activityIds);

    public function findByCourseSetId($courseSetId);

    public function findByIds($ids);

    public function findByCourseIdAndCategoryId($courseId, $categoryId);

    public function findByCourseIdAndIsFree($ids, $isFree);

    public function findByCopyIdAndLockedCourseIds($copyId, $courseIds);

    public function findByCopyIdSAndLockedCourseIds($copyIds, $courseIds);

    public function getMaxSeqByCourseId($courseId);

    public function getNumberSeqByCourseId($courseId);

    public function getNextTaskByCourseIdAndSeq($courseId, $seq);

    public function getPreTaskByCourseIdAndSeq($courseId, $seq);

    public function getByChapterIdAndMode($chapterId, $mode);

    public function findByChapterId($chapterId);

    public function getMinSeqByCourseId($courseId);

    public function getByCourseIdAndSeq($courseId, $sql);

    public function getByCopyId($copyId);

    public function getByCourseIdAndCopyId($courseId, $copyId);

    /**
     * 统计当前时间以后每天的直播次数.
     *
     * @param  $limit
     *
     * @return array <string, int|string>
     */
    public function findFutureLiveDates($limit);

    /**
     * 返回过去直播过的课程ID.
     *
     * @return array<int>
     */
    public function findPastLivedCourseSetIds();

    public function getTaskByCourseIdAndActivityId($courseId, $activityId);

    public function sumCourseSetLearnedTimeByCourseSetId($courseSetId);

    public function countLessonsWithMultipleTasks($courseId);

    public function analysisTaskDataByTime($startTime, $endTime);

    public function countByChpaterId($chapterId);
}
