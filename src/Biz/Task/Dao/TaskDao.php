<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskDao extends GeneralDaoInterface
{
    public function deleteByCategoryId($categoryId);

    public function findByCourseId($courseId);

    public function findByCourseIds($courseIds);

    public function findByActivityIds($activityIds);

    public function findByIds($ids);

    public function findByCourseIdAndIsFree($ids, $isFree);

    public function getMaxSeqByCourseId($courseId);

    public function getNextTaskByCourseIdAndSeq($courseId, $seq);

    public function getPreTaskByCourseIdAndSeq($courseId, $seq);

    public function getByChapterIdAndMode($chapterId, $mode);

    public function findByChapterId($chapterId);

    public function getMinSeqByCourseId($courseId);

    public function getByCourseIdAndSeq($courseId, $sql);

    public function getTaskByCourseIdAndActivityId($courseId, $activityId);

    public function getLearnTimeByCourseSetId($courseSetId);
}
