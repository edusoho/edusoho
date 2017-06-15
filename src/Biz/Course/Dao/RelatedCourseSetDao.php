<?php

namespace Biz\Course\Dao;

interface RelatedCourseSetDao
{
    public function pickRelatedCourseSetIdsByTags($tagIds, $count, $excludeCourseSetId = 0);
}
