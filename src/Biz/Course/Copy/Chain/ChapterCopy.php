<?php

namespace Biz\Course\Copy\Chain;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseChapterDao;

class ChapterCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        $courseId = $source['id'];
        $newCourseId = $config['newCourse']['id'];
        $isCopy = $config['isCopy'];
        //查询出course下所有chapter，新增并保留新旧chapter id，用于填充newTask的categoryId
        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        if (empty($chapters)) {
            return array();
        }

        $chapterMap = array(); // key=oldChapterId,value=newChapter

        foreach ($chapters as $chapter) {
            $newChapter = $this->filterFields($chapter);
            $newChapter['courseId'] = $newCourseId;
            $newChapter['copyId'] = $isCopy ? $chapter['id'] : 0;
            $newChapter = $this->getChapterDao()->create($newChapter);
            $chapterMap[$chapter['id']] = $newChapter;
        }

        return $chapterMap;
    }

    protected function getFields()
    {
        return array(
            'type',
            'number',
            'seq',
            'title',
            'status',
            'isOptional',
            'published_number',
        );
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }
}
