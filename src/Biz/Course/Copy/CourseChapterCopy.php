<?php

namespace Biz\Course\Copy;

use Biz\AbstractCopy;
use Biz\Course\Dao\CourseChapterDao;

class CourseChapterCopy extends AbstractCopy
{
    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($source, $options)
    {
        $newCourse = $options['newCourse'];
        $course = $options['originCourse'];
        $chapters = $this->getChapterDao()->findChaptersByCourseId($course['id']);

        if (empty($chapters)) {
            return array();
        }

        $chapterMap = array();
        foreach ($chapters as $chapter) {
            $newChapter = $this->partsFields($chapter);
            $newChapter['courseId'] = $newCourse['id'];
            $newChapter['copyId'] = $chapter['id'];
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
