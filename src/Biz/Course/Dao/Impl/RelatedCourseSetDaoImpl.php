<?php

namespace Biz\Course\Dao\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\RelatedCourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RelatedCourseSetDaoImpl extends GeneralDaoImpl implements RelatedCourseSetDao
{
    public function pickRelatedCourseSetIdsByTags($tagIds, $count, $excludeCourseSetId = 0)
    {
        if (empty($tagIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($tagIds) - 1).'?';

        $sql = "SELECT courseSet.id ,COUNT(1) count 
              FROM `tag_owner` tag  LEFT JOIN course_set_v8 courseSet
              on tag.ownerId = courseSet.id
              WHERE tag.tagId in ({$marks}) AND tag.ownerType = 'course-set'
              AND courseSet.status = 'published'
              AND courseSet.parentId = 0
              AND courseSet.id != ?
              GROUP by courseSet.id order by count  desc ,courseSet.id desc";
        $sql = $this->sql($sql, array(), 0, $count);

        $restult = $this->db()->fetchAll($sql, array_merge($tagIds, array($excludeCourseSetId))) ?: array();

        return ArrayToolkit::column($restult, 'id');
    }

    public function declares()
    {
    }
}
