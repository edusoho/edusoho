<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;
use Biz\Course\Copy\MaterialCopy;
use Biz\Course\Dao\CourseMaterialDao;

class MaterialCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new MaterialCopy($this->biz, array(), false);

        $this->assertNull($copy->preCopy(array(), array()));
    }

    public function testDoCopy()
    {
        $material = array(
            'courseId' => 0,
            'lessonId' => 0,
            'title' => '常用的邮箱服务器SMTP地址.pdf',
            'source' => 'coursematerial',
            'courseSetId' => 3,
            'fileId' => '1',
        );
        $this->getMaterialDao()->create($material);
        $source = array('id' => 3);
        $options = array(
            'newCourseSet' => array('id' => 5),
        );

        $copy = new MaterialCopy($this->biz, array(), false);
        $copy->doCopy($source, $options);

        $materials = $this->getMaterialDao()->search(
            array('courseSetId' => 5),
            array(),
            0,
            PHP_INT_MAX
        );
        $this->assertEquals($materials[0]['source'], 'coursematerial');
    }

    protected function getMaterialDao()
    {
        return $this->createDao('Course:CourseMaterialDao');
    }
}
