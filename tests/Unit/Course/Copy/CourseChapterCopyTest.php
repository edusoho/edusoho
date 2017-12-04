<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseMemberCopy;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Copy\CourseChapterCopy;

class CourseChapterCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseChapterCopy($this->biz, array(), false);

        $this->assertNull($copy->preCopy(array(), array()));
    }

    public function testDoCopy()
    {
        $chapter = array(
            'courseId' => 3,
            'type' => 'chapter',
            'title' => '章节',
            'seq' => 10,
            'number' => 44,
        );
        $this->getChapterDao()->create($chapter);
        $options = array(
            'newCourse' => array('id' => 2),
            'originCourse' => array('id' => 1),
        );

        $copy = new CourseChapterCopy($this->biz, array(), false);
        $chapterMap = $copy->doCopy(array(), $options);
        $this->assertEmpty($chapterMap);

        $chapter['courseId'] = 1;
        $this->getChapterDao()->create($chapter);
        $chapterMap = $copy->doCopy(array(), $options);

        $this->assertEquals(1, count($chapterMap));
        $chaptersByCourseId = $this->getChapterDao()->findChaptersByCourseId(1);
        $this->assertEquals(1, count($chaptersByCourseId)); 

        $chapterMap = reset($chapterMap);
        foreach($this->getDefaultFields() as $column) {
            $this->assertEquals($chapter[$column], $chapterMap[$column]); 
        }
    } 

    private function getDefaultFields()
    {
        return array(
            'type',
            'number',
            'seq',
            'title',
        );
    }

    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }
}
