<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Topxia\Common\Exception\AccessDeniedException;
use Topxia\Common\Exception\ResourceNotFoundException;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourse($id)
    {
        return $this->getCourseDao()->get($id);
    }

    public function findCoursesByCourseSetId($courseSetId)
    {
        return $this->getCourseDao()->findCoursesByCourseSetId($courseSetId);
    }

    public function getDefaultCourseByCourseSetId($courseSetId)
    {
        $courses = $this->findCoursesByCourseSetId($courseSetId);
        if (empty($courses)) {
            return null;
        }
        foreach ($courses as $course) {
            if ($course['isDefault']) {
                return $course;
            }
        }
        return null;
    }

    public function createCourse($course)
    {
        $course = ArrayToolkit::parts($course, array(
            'title',
            'courseSetId',
            'learnMode',
            'expiryMode'
        ));
        $course = $this->validateCourse($course);
        //TODO 确认下是否需要判重，另外，应该查找同一个courseSetId下的courses
        $existCourses = $this->getCourseDao()->findCoursesByTitle($course['title']);
        if (!empty($existCourses)) {
            throw new InvalidArgumentException('标题已被占用');
        }

        $course['status']      = 'draft';
        $course['auditStatus'] = 'draft';

        return $this->getCourseDao()->create($course);
    }

    public function updateCourse($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'courseSetId',
            'learnMode',
            'expiryMode',
            'expiryDays',
            'expiryStartDate',
            'expiryEndDate',
            'summary',
            'goals',
            'audiences'
        ));
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['status'] == 'published') {
            unset($fields['learnMode']);
            unset($fields['expiryMode']);
            unset($fields['expiryDays']);
            unset($fields['expiryStartDate']);
            unset($fields['expiryEndDate']);
        }
        $fields = $this->validateCourse($fields);

        return $this->getCourseDao()->update($id, $fields);
    }

    public function copyCourse($copyId, $course)
    {
        //TODO
        //validator basic info of $course
        //copy tasks、marketing from copyCourse
        //save basic info,tasks,marketing

        $course['copyCourseId'] = $copyId;
        return $this->getCourseDao()->create($course);
    }

    public function deleteCourse($id)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['status'] == 'published') {
            throw new AccessDeniedException('已发布的教学计划不允许删除');
        }

        return $this->getCourseDao()->delete($id);
    }

    public function closeCourse($id)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['status'] != 'published') {
            throw new AccessDeniedException('教学计划尚未发布');
        }
        $course['status'] = 'closed';

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
            throw new AccessDeniedException('只允许发布未发布教学计划');
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
            'auditStatus' => 'committed'
        ));
    }

    public function auditPublishment($id, $userId, $reject, $remark)
    {
        $course = $this->getCourseDao()->get($id);
        if (empty($course)) {
            throw new ResourceNotFoundException('Course', $id);
        }
        if ($course['auditStatus'] !== 'committed') {
            throw new AccessDeniedException('无法审核该教学计划');
        }
        $result = $reject ? 'reject' : 'accept';
        $audit  = array(
            'courseId'    => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'status'      => $result,
            'creator'     => $userId,
            'remark'      => $remark
        );

        $this->getCourseAuditDao()->create($audit);
        $courseResult = array(
            'auditStatus' => $result,
            'auditRemark' => $remark
        );
        if ($reject) {
            $courseResult['status'] = 'published';
        }
        $this->getCourseDao()->update($id, $courseResult);
    }

    protected function validateCourse($course)
    {
        if ($course['expiryMode'] == 'days') {
            unset($course['expiryStartDate']);
            unset($course['expiryEndDate']);
        } else {
            unset($course['expiryDays']);
            if (isset($course['expiryStartDate'])) {
                $course['expiryStartDate'] = strtotime($course['expiryStartDate']);
            } else {
                throw new InvalidArgumentException('有效期的开始日期不能为空');
            }
            if (isset($course['expiryEndDate'])) {
                $course['expiryEndDate'] = strtotime($course['expiryEndDate']);
            } else {
                throw new InvalidArgumentException('有效期的截止日期不能为空');
            }
            if ($course['expiryEndDate'] <= $course['expiryStartDate']) {
                throw new InvalidArgumentException('有效期的截止日期需晚于开始日期');
            }
        }

        if (empty($course['title'])) {
            throw new InvalidArgumentException('标题不能为空');
        }

        return $course;
    }

    protected function getCourseAuditDao()
    {
        return $this->createDao('Course:CourseAuditDao');
    }

    protected function getCourseMarketingDao()
    {
        return $this->createDao('Course:CourseMarketingDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
