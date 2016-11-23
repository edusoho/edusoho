<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskDao extends GeneralDaoInterface
{
    public function findByCourseId($courseId);

    public function getByCourseIdAndActivityId($courseId, $activity);

    public function getByCourseIdAndSeq($courseId, $seq);

    public function getMaxSeqByCourseId($courseId);

    public function findTasksByChapterId($chapterId);

    public function waveSeqBiggerThanSeq($seq, $diff);
}
