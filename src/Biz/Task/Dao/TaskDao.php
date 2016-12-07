<?php

namespace Biz\Task\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TaskDao extends GeneralDaoInterface
{
    public function deleteByCategoryId($categoryId);

    public function findByCourseId($courseId);

    public function getMaxSeqByCourseId($courseId);

    public function getNextTaskByCourseIdAndSeq($courseId, $seq);

    public function getPreTaskByCourseIdAndSeq($courseId, $seq);

    public function getByChapterIdAndMode($chapterId, $mode);

    public function findByChapterId($chapterId);
}
