<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Dao\CourseChapterDao;

class ChapterSync extends AbstractEntitySync
{
    protected function syncEntity($source, $config = [])
    {
        $newCourseId = $config['newCourse']['id'];
        //查询出course下所有chapter，新增并保留新旧chapter id，用于填充newTask的categoryId
        $chapters = $source['chapterList'];
        if (empty($chapters)) {
            return [];
        }

        $chapterMap = [];
        foreach ($chapters as $chapter) {
            $newChapter = $this->filterFields($chapter);
            $newChapter['courseId'] = $newCourseId;
            $newChapter['copyId'] = 0;
            $newChapter['syncId'] = $chapter['id'];
            $newChapter = $this->getChapterDao()->create($newChapter);
            $chapterMap[$chapter['id']] = $newChapter;
        }

        return $chapterMap;
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $newCourseId = $config['newCourse']['id'];
        $chapters = $source['chapterList'];
        $existChapters = ArrayToolkit::index($this->getChapterDao()->findChaptersByCourseId($newCourseId), 'syncId');
        if (empty($chapters)) {
            foreach ($existChapters as $existChapter) {
                $this->getChapterDao()->delete($existChapter['id']);
            }

            return [];
        }

        $chapterMap = [];
        foreach ($chapters as $chapter) {
            $newChapter = $this->filterFields($chapter);
            $newChapter['courseId'] = $newCourseId;
            $newChapter['copyId'] = 0;
            $newChapter['syncId'] = $chapter['id'];
            if (!empty($existChapters[$newChapter['syncId']])) {
                $newChapter = $this->getChapterDao()->update($existChapters[$newChapter['syncId']]['id'], $newChapter);
            } else {
                $newChapter = $this->getChapterDao()->create($newChapter);
            }
            $chapterMap[$chapter['id']] = $newChapter;
        }

        $needDeleteChapterSyncIds = array_values(array_diff(array_keys($existChapters), array_keys($chapterMap)));
        if (!empty($existChapters) && !empty($needDeleteChapterSyncIds)) {
            $needDeleteChapters = $this->getChapterDao()->search(['courseId' => $newCourseId, 'syncIds' => $needDeleteChapterSyncIds], [], 0, PHP_INT_MAX);
            foreach ($needDeleteChapters as $needDeleteChapter) {
                $this->getChapterDao()->delete($needDeleteChapter['id']);
            }
        }

        return $chapterMap;
    }

    protected function getFields()
    {
        return [
            'type',
            'number',
            'seq',
            'title',
            'status',
            'isOptional',
            'published_number',
        ];
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }
}
