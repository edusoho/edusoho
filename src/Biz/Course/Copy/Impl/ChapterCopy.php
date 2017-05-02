<?php

namespace Biz\Course\Copy\Impl;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Course\Dao\CourseChapterDao;

class ChapterCopy extends AbstractEntityCopy
{
    public function __construct($biz)
    {
        parent::__construct($biz, 'chapter');
    }

    protected function copyEntity($source, $config = array())
    {
        $courseId = $source['id'];
        $newCourseId = $config['newCourse']['id'];
        $isCopy = $config['isCopy'];
        //查询出course下所有chapter，新增并保留新旧chapter id，用于填充newTask的categoryId
        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        $chapterMap = array(); // key=oldChapterId,value=newChapter
        if (!empty($chapters)) {
            //order by parentId
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
                $newChapter = $this->copyFields($chapter);
                $newChapter['courseId'] = $newCourseId;
                $newChapter['copyId'] = $isCopy ? $chapter['id'] : 0;

                if ($chapter['parentId'] > 0) {
                    $newChapter['parentId'] = $chapterMap[$chapter['parentId']]['id'];
                }
                $newChapter = $this->getChapterDao()->create($newChapter);
                $chapterMap[$chapter['id']] = $newChapter;
            }
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
