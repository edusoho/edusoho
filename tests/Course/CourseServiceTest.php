<?php

namespace Tests;

use Topxia\Service\Common\BaseTestCase;

class CourseServiceTest extends BaseTestCase
{
	private const $global_user_id = 1;
	private const $global_course_set_id = 1;

    public function testCreateAndGet()
    {
        //TODO
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);

        $created = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($result['title'], $created['title']);
    }

    public function testUpdate()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        $result['title'] = '第一个教学计划(改)';

        $updated = $this->getCourseService()->updateCourse($result['id'], $result);

        $this->assertEquals($updated['title'], $result['title']);
    }

    public function testDelete()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        $deleted = $this->getCourseService()->deleteCourse($result['id']);

        $this->assertEquals($deleted, 1);
    }

    public function testCopyCourse()
    {
        //add a course and copy it
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        //可修改基本信息
        $courseCopy = array(
            'title'       => '第一个教学计划(复制)',
            'courseSetId' => 1
        );
        $copyed = $this->getCourseService()->copyCourse($result['id'], $courseCopy);

        $this->assertNotNull($copyed);
        $this->assertEquals($copyed['copyId'], $result['id']);
    }

    public function testCloseCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->closeCourse($result['id']);

        $closed = $this->getCourseService()->getCourse($result['id']);

        $this->assertTrue($closed['status'] == 'closed');
    }

    public function testSaveCourseMarketing()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        $marketing = array(
            'courseId'      => $result['id'],
            'isFree'        => 0,
            'price'         => 11.9,
            'joinMode'      => 1,
            'enableTrylook' => 1,
            'trylookLength' => 10,
            'services'      => "['absc','edf','ggg']"
        );

        $saved = $this->getCourseService()->testSaveCourseMarketing($marketing);

        $this->assertNotNull($saved['id']);
        $this->assertEquals($saved['courseId'], $result['id']);
    }

    public function testPreparePublishment()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->prepatePublishment($result['id'], 1);

        $prepared = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($prepared['auditStatus'], 'committed');
    }

    public function testAuditPublishment()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1
        );

        $result = $this->getCourseService()->createCourse($course);
		$this->getCourseService()->prepatePublishment($result['id'], 1);
		$this->getCourseService()->auditPublishment($result['id'], $global_user_id, true, '异议！驳回！');

		$rejected = $this->getCourseService()->getCourse($result['id']);
		$this->assertEquals($rejected['auditStatus'], 'rejected');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }
}
