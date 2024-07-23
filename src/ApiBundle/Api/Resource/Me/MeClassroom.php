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
        $members = ArrayToolkit::index($members, 'classroomId');
        if (isset($querys['format']) && 'pagelist' == $querys['format']) {
            list($offset, $limit) = $this->getOffsetAndLimit($request);
            $classroomConditions = $this->buildClassroomConditions($members, $querys);
            if (empty($classroomConditions['ids'])) {
                return $this->makePagingObject([], 0, $offset, $limit);
            }
            $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions, [], 0, PHP_INT_MAX);
            $orderedClassroomIds = array_column($members, 'classroomId');
            $indexClassroom = ArrayToolkit::index($classrooms, 'id');
            $orderedClassroom = [];
            foreach ($orderedClassroomIds as $orderedClassroomId) {
                if (!empty($indexClassroom[$orderedClassroomId])) {
                    $orderedClassroom[] = $indexClassroom[$orderedClassroomId];
                }
            }
            $classrooms = array_slice($orderedClassroom, $offset, $limit);
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);

            foreach ($classrooms as &$classroom) {
                $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $this->getCurrentUser()->getId());
                $classroom['learningProgressPercent'] = $progress['percent'];
                $classroom['isExpired'] = empty($members[$classroom['id']]) || $this->isExpired($members[$classroom['id']]['deadline']);
            }

            return $this->makePagingObject($classrooms, $this->getClassroomService()->countClassrooms($classroomConditions), $offset, $limit);
        } else {
            $classroomIds = ArrayToolkit::column($members, 'classroomId');

            $classrooms = array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);

            foreach ($classrooms as &$classroom) {
                $classroom['lastLearnTime'] = $members[$classroom['id']]['createdTime'];
                $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $this->getCurrentUser()->getId());
                $classroom['learningProgressPercent'] = $progress['percent'];
                $classroom['isExpired'] = empty($members[$classroom['id']]) || $this->isExpired($members[$classroom['id']]['deadline']);
            }

            array_multisort(ArrayToolkit::column($classrooms, 'lastLearnTime'), SORT_DESC, $classrooms);

            return $classrooms;
        }
    }

    private function buildClassroomConditions($members, $querys)
    {
        $classroomConditions = [];
        if (!isset($querys['type'])) {
            $classroomConditions['ids'] = ArrayToolkit::column($members, 'classroomId');

            return $classroomConditions;
        }

        $learningIds = [];
        $learnedIds = [];
        $isExpiredIds = [];
        foreach ($members as $member) {
            if ($this->isExpired($member['deadline'])) {
                $isExpiredIds[] = $member['classroomId'];
            } elseif ($member['isFinished']) {
                $learnedIds[] = $member['classroomId'];
            } else {
                $learningIds[] = $member['classroomId'];
            }
        }
        $classroomConditions['titleLike'] = $querys['title'];
        switch ($querys['type']) {
            case 'learning':
            case 'learned':
                $classroomConditions['ids'] = 'learning' === $querys['type'] ? $learningIds : $learnedIds;
                $classroomConditions['excludeStatus'] = 'closed';
                break;
            default:
                $closedClassroomIds = $this->getClassroomService()->searchClassrooms(['status' => 'closed', 'ids' => array_merge($learningIds, $learnedIds)], [], 0, PHP_INT_MAX, ['id']);
                $classroomConditions['ids'] = array_merge($isExpiredIds, array_column($closedClassroomIds, 'id'));
                break;
        }

        return $classroomConditions;
    }

    private function isExpired($deadline)
    {
        return 0 != $deadline && $deadline < time();
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
