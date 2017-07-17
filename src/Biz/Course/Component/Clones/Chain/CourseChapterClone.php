<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Biz\Course\Dao\CourseChapterDao;

class CourseChapterClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseChapters($source, $options);
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

    private function cloneCourseChapters($source, $options)
    {
        $chapters = $this->getChapterDao()->findChaptersByCourseId($source['id']);

        if (empty($chapters)) {
            return array();
        }

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
            $newChapter = $this->filterFields($chapter);
            $newChapter['courseId'] = $source['id'];

            if ($chapter['parentId'] > 0) {
                $newChapter['parentId'] = $chapterMap[$chapter['parentId']]['id'];
            }
            $newChapter = $this->getChapterDao()->create($newChapter);
            $chapterMap[$chapter['id']] = $newChapter;
        }

        return $chapterMap;
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }
}
