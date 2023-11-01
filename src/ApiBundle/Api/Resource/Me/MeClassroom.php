<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\LearningDataAnalysisService;

class MeClassroom extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $querys = $request->query->all();

        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
            'role' => 'student',
        ];
        $orderBy = [
            'lastLearnTime' => 'desc',
            'createdTime' => 'desc',
        ];
        $members = $this->getClassroomService()->searchMembers($conditions, $orderBy, 0, PHP_INT_MAX);
        if (isset($querys['format']) && 'pagelist' == $querys['format']) {
            list($offset, $limit) = $this->getOffsetAndLimit($request);
            $classroomConditions = $this->buildClassroomConditions($members, $querys);
            $classrooms = array_values($this->getClassroomService()->searchClassrooms($classroomConditions, [], $offset, $limit));
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);

            foreach ($classrooms as &$classroom) {
                $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $this->getCurrentUser()->getId());
                $classroom['learningProgressPercent'] = $progress['percent'];
                $classroom['isExpired'] = 0 !== $classroom['deadline'] && $classroom['deadline'] < time();
            }

            return $this->makePagingObject($classrooms, $this->getClassroomService()->countClassrooms($classroomConditions), $offset, $limit);
        } else {
            $classroomIds = ArrayToolkit::column($members, 'classroomId');

            $classrooms = array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);
            $members = ArrayToolkit::index($members, 'classroomId');

            foreach ($classrooms as &$classroom) {
                $classroom['lastLearnTime'] = $members[$classroom['id']]['createdTime'];
                $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $this->getCurrentUser()->getId());
                $classroom['learningProgressPercent'] = $progress['percent'];
                $classroom['isExpired'] = 0 !== $classroom['deadline'] && $classroom['deadline'] > time();
            }

            array_multisort(ArrayToolkit::column($classrooms, 'lastLearnTime'), SORT_DESC, $classrooms);

            return $classrooms;
        }
    }

    private function buildClassroomConditions($members, $querys)
    {
        $courseConditions = [];
        if (!isset($querys['type'])) {
            $courseConditions['ids'] = ArrayToolkit::column($members, 'classroomId');

            return $courseConditions;
        }

        $learningIds = [];
        $learnedIds = [];
        $isExpiredIds = [];
        foreach ($members as &$member) {
            $deadline = intval($member['deadline']);
            if (0 !== $deadline && $deadline < time()) {
                $isExpiredIds[] = $member['classroomId'];
            } elseif ($member['isFinished']) {
                $learnedIds[] = $member['classroomId'];
            } else {
                $learningIds[] = $member['classroomId'];
            }
        }
        switch ($querys['type']) {
            case 'learning':
            case 'learned':
                $courseConditions['ids'] = 'learning' === $querys['type'] ? $learningIds : $learnedIds;
                $courseConditions['status'] = 'published';
                break;
            default:
                $closedClassroomIds = ArrayToolkit::column(
                    $this->getClassroomService()->searchClassrooms(['status' => 'closed', 'ids' => array_merge($learningIds, $learnedIds)], [], 0, PHP_INT_MAX),
                    'id'
                );
                $courseConditions['ids'] = array_merge($isExpiredIds, $closedClassroomIds);
                break;
        }

        return $courseConditions;
    }

    private function getClassrooms($conditions, $orderBy, $offset, $limit)
    {
        $classroomIds = ArrayToolkit::column(
            $this->getClassroomService()->searchMembers($conditions, $orderBy, $offset, $limit),
            'classroomId'
        );

        return array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->service('Classroom:LearningDataAnalysisService');
    }
}
