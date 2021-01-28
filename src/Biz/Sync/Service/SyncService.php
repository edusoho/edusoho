<?php

namespace Biz\Sync\Service;

interface SyncService
{
    /**
     * @param $alias 'Course:CourseChapter.syncWhenCreate'
     * @param $sourceId
     *
     * @return mixed
     */
    public function sync($alias, $sourceId);
}
