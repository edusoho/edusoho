<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Job\CourseDataCleanJob;

class CourseDataCleanJobTest extends BaseTestCase
{
    public function testExcute()
    {
        $this->getCourseDao()->create(
            array(
                'title' => 'course_title1',
                'courseSetId' => 12,
                'id' => 2221,
            )
        );

        $this->getCourseDao()->create(
            array(
                'title' => 'course_title2',
                'courseSetId' => 12,
                'id' => 2222,
            )
        );

        $memberService = $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'findMemberUserIdsByCourseId',
                    'withParams' => array(2221),
                    'returnValue' => array(1, 2, 3),
                    'times' => 1,
                ),
                array(
                    'functionName' => 'findMemberUserIdsByCourseId',
                    'withParams' => array(2222),
                    'returnValue' => array(1, 3, 4),
                    'times' => 2,
                ),
            )
        );

        $analysisDao = $this->mockBiz(
            'Course:LearningDataAnalysisDao',
            array(
                array(
                    'functionName' => 'batchRefreshUserLearningData',
                    'withParams' => array(2221, array(1, 2, 3)),
                ),
                array(
                    'functionName' => 'batchRefreshUserLearningData',
                    'withParams' => array(2222, array(1, 3, 4)),
                ),
            )
        );

        $job = new CourseDataCleanJob(array(), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $memberService->shouldHaveReceived('findMemberUserIdsByCourseId')->times(2);
        $analysisDao->shouldHaveReceived('batchRefreshUserLearningData')->times(2);
    }

    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
