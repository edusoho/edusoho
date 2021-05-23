<?php

namespace Biz\Certificate\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Certificate\Strategy\BaseStrategy;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;

class ClassroomStrategy extends BaseStrategy
{
    public function getTargetModal()
    {
        return 'admin-v2/operating/certificate/target/classroom-modal.html.twig';
    }

    public function count($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getClassroomService()->countClassrooms($conditions);
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getClassroomService()->searchClassrooms($conditions, $orderBys, $start, $limit);
    }

    public function getTarget($targetId)
    {
        return $this->getClassroomService()->getClassroom($targetId);
    }

    public function findTargetsByIds($targetIds)
    {
        return $this->getClassroomService()->findClassroomsByIds($targetIds);
    }

    public function findTargetsByTargetTitle($targetTitle)
    {
        $count = $this->getClassroomService()->countClassrooms(['titleLike' => $targetTitle]);

        return $this->getClassroomService()->searchClassrooms(
            ['titleLike' => $targetTitle],
            [],
            0,
            $count
        );
    }

    public function issueCertificate($certificate)
    {
        $classroom = $this->getClassroomService()->getClassroom($certificate['targetId']);
        $members = $this->getClassroomService()->findClassroomStudents($classroom['id'], 0, PHP_INT_MAX);
        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        $courseIds = ArrayToolkit::column($courses, 'id');
        $finishUserIds = [];
        foreach ($members as $member) {
            $memberCounts = $this->getCourseMemberService()->countMembers(['finishedTime_GT' => 0, 'userId' => $member['userId'], 'courseIds' => $courseIds]);
            if ($memberCounts >= count($courseIds) && !empty($memberCounts)) {
                $finishUserIds[] = $member['userId'];
            }
        }
        $batches = array_chunk($finishUserIds, self::ISSUE_LIMIT);
        foreach ($batches as $userIds) {
            $this->getRecordService()->autoIssueCertificates($certificate['id'], $userIds);
        }
    }

    public function updateCertificateTargetStatus($targetId, $status)
    {
        if (in_array($status, ['published', 'unpublished'])) {
            $certificates = $this->getCertificateService()->findByTargetIdAndTargetType($targetId, 'classroom');
            foreach ($certificates as $certificate) {
                $this->getCertificateService()->update($certificate['id'], ['targetStatus' => $status]);
            }
        }
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['keyword'])) {
            $conditions['titleLike'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        $conditions['status'] = 'published';

        return $conditions;
    }

    protected function getContent($record, $content)
    {
        $content = $this->getRecipientContent($record['userId'], $content);

        if (strstr($content, '$classroomName$')) {
            $classroom = $this->getClassroomService()->getClassroom($record['targetId']);
            $content = str_replace('$classroomName$', $classroom['title'], $content);
        }

        return $content;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
