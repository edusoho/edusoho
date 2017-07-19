<?php

namespace Biz\Synchronization\Service;

interface SynchronizationService
{
    /**
     * @param $module 'Course:CourseChapter.Create'
     * @param $sourceId
     * @return mixed
     */
    public function sync($module, $sourceId);
}
