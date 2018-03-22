<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Job\UpdateCourseSetMonthStudentNumJob;

class UpdateCourseSetMonthStudentNumJobTest extends BaseTestCase
{
    public function testExecuteWithOutCourseJob()
    {
        $fields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
            'monthStudentNum' => 10,
        );
        $courseSet = $this->getCourseSetDao()->create($fields);
        $this->assertEquals($fields['monthStudentNum'], $courseSet['monthStudentNum']);

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'searchMemberCountGroupByFields',
                'returnValue' => array(array('courseSetId' => $courseSet['id'], 'count' => 2)),
            ),
        ));

        $job = new UpdateCourseSetMonthStudentNumJob(array(), $this->getBiz());
        $job->execute();

        $result = $this->getCourseSetDao()->get($courseSet['id']);
        $this->assertEquals(2, $result['monthStudentNum']);
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
