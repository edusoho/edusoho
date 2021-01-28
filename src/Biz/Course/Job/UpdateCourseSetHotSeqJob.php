<?php

namespace Biz\Course\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\Goods\Service\RecommendGoodsService;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateCourseSetHotSeqJob extends AbstractJob
{
    public function execute()
    {
        $this->getGoodsService()->refreshGoodsHotSeq();
        $coursesStudentsCount = $this->calCoursesStudentsCount();
        $this->updateCourseHotSeq($coursesStudentsCount);
        $this->getRecommendGoodsService()->refreshGoodsHotSeqByProductTypeAndProductMemberCount(
            'course',
            ArrayToolkit::index($coursesStudentsCount, 'courseSetId')
        );

        $classroomsStudentsCount = $this->calClassroomsStudentsCount();
        $this->updateClassroomHotSeq($classroomsStudentsCount);
        $this->getRecommendGoodsService()->refreshGoodsHotSeqByProductTypeAndProductMemberCount(
            'classroom',
            ArrayToolkit::index($classroomsStudentsCount, 'classroomId')
        );
    }

    private function calCoursesStudentsCount()
    {
        return $this->getCourseMemberService()->searchMemberCountGroupByFields(
            [
                'startTimeGreaterThan' => strtotime('-30 days'),
                'classroomId' => 0,
                'role' => 'student',
            ],
            'courseSetId',
            0,
            PHP_INT_MAX
        );
    }

    protected function updateCourseHotSeq($coursesStudentsCount)
    {
        //把所有课程的hotSeq都更新为0
        $this->getCourseSetService()->refreshHotSeq();

        if (!empty($coursesStudentsCount)) {
            $batchHelper = new BatchUpdateHelper($this->getCourseSetDao());

            foreach ($coursesStudentsCount as $count) {
                $fields = ['hotSeq' => $count['count']];
                $batchHelper->add('id', $count['courseSetId'], $fields);
            }

            $batchHelper->flush();
        }
    }

    private function calClassroomsStudentsCount()
    {
        return $this->getClassroomService()->searchMemberCountGroupByFields(
            [
                'createdTime_GE' => strtotime('-30 days'),
                'roles' => ['student', 'assistant'],
            ],
            'classroomId',
            0,
            PHP_INT_MAX
        );
    }

    protected function updateClassroomHotSeq($classroomsStudentsCount)
    {
        $this->getClassroomService()->refreshClassroomHotSeq();

        if (!empty($classroomsStudentsCount)) {
            $batchHelper = new BatchUpdateHelper($this->getClassroomDao());

            foreach ($classroomsStudentsCount as $count) {
                $fields = ['hotSeq' => $count['count']];
                $batchHelper->add('id', $count['classroomId'], $fields);
            }

            $batchHelper->flush();
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return RecommendGoodsService
     */
    protected function getRecommendGoodsService()
    {
        return $this->biz->service('Goods:RecommendGoodsService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    protected function getClassroomDao()
    {
        return $this->biz->dao('Classroom:ClassroomDao');
    }
}
