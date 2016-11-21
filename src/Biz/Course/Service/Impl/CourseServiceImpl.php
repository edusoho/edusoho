<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByCourseId($courseId)
    {
        return $this->getCourseDao->findByCourseId($courseId);
    }

    public function createCourse($course)
    {
        //TODO validator

        return $this->getCourseDao()->create($course);
    }

    public function updateCourse($id, $fields)
    {
        //TODO validator

        return $this->getCourseDao()->update($id, $fields);
    }

    public function copyCourse($copyId, $course)
    {
        //TODO
        //validator basic info of $course
        //copy tasks、marketing from copyCourse
        //save basic info,tasks,marketing
    }

    public function deleteCourse($id)
    {
        //TODO
        //validator if course can be deleted

        return $this->getCourseDao()->delete($id);
    }

    public function closeCourse($id)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        //TODO define status ？
        $course['status'] = 0;

        $this->getCourseDao()->update($id, $course);
    }

    public function saveCourseMarketing($courseMarketing)
    {
        //TODO validator

        if (isset($courseMarketing)) {
            $this->getCourseMarketingDao()->create($courseMarketing);
        } else {
            $this->getCourseMarketingDao()->update($id, $courseMarketing);
        }
    }

    public function preparePublishment($id, $userId)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['auditStatus'] !== 'draft') {
            throw new IllegalOperationException('Course', $id, 'Audit');
        }

        $audit = array(
            'courseId'    => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'status'      => 'committed',
            'creator'     => $userId,
            'remark'      => '提交审核'
        );

        $this->getCourseAuditDao()->create($audit);
        $this->getCourseDao()->update($id, array(
            'auditStatus' => 'process'
        ));
    }

    public function auditPublishment($id, $userId, $reject, $remark)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['auditStatus'] !== 'committed') {
            throw new IllegalOperationException('Course', $id, 'Audit');
        }
        $result = $reject ? 'reject' : 'pass';
        $audit  = array(
            'courseId'    => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'status'      => $result,
            'creator'     => $userId,
            'remark'      => $remark
        );

        $this->getCourseAuditDao()->create($audit);
        $this->getCourseDao()->update($id, array(
            'auditStatus' => $result,
            'auditRemark' => $remark
        ));
    }

    protected function getCourseMarketingDao()
    {
        return $this->createDao('Course:CourseMarketingDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }
}
