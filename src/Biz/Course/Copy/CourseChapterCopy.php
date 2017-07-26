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
        $course = $options['course'];
        $chapters = $this->getChapterDao()->findChaptersByCourseId($course['id']);

        if (empty($chapters)) {
            return array();
        }
//
//        foreach($chapters as $chapter) {
//
//        }

        $chapterMap = array();
        //章节具有父子关系，在创建的时候，不能批量创建，同时要按照顺序排序
        usort($chapters, function ($a, $b) {
            if ($a['parentId'] < $b['parentId']) {
                return -1;
            }
            if ($a['parentId'] == $b['parentId']) {
                return $a['id'] > $b['id'];
            }

            return 1;
        });
        foreach ($chapters as $chapter) {
            $newChapter = $this->partsFields($chapter);
            $newChapter['courseId'] = $newCourse['id'];

            if ($chapter['parentId'] > 0) {
                $newChapter['parentId'] = $chapterMap[$chapter['parentId']]['id'];
            }
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