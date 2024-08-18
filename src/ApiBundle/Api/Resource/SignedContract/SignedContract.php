<?php

namespace ApiBundle\Api\Resource\SignedContract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Contract\Service\ContractService;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;

class SignedContract extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        list($abort, $conditions) = $this->buildSearchConditions($request->query->all());
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        if ($abort) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }
        $signedContracts = $this->getContractService()->searchSignedContracts($conditions, ['id' => 'DESC'], $offset, $limit, ['id', 'userId', 'goodsKey', 'snapshot', 'createdTime']);

        return $this->makePagingObject($this->wrap($signedContracts), $this->getContractService()->countSignedContracts($conditions), $offset, $limit);
    }

    public function get(ApiRequest $request, $id)
    {
    }

    private function buildSearchConditions($query)
    {
        $goodsTypes = ['course', 'classroom', 'itemBankExercise'];
        if (!empty($query['goodsType']) && !in_array($query['goodsType'], $goodsTypes)) {
            return [true, []];
        }
        $conditions = [
            'goodsType' => $query['goodsType'] ?? '',
        ];
        if (!empty($query['signTimeFrom'])) {
            $conditions['createdTime_GTE'] = $query['signTimeFrom'];
        }
        if (!empty($query['signTimeTo'])) {
            $conditions['createdTime_LTE'] = $query['signTimeTo'];
        }
        if (empty($query['keywordType']) || (empty($query['keyword']) && '0' != $query['keyword'])) {
            return [false, $conditions];
        }
        if (in_array($query['keywordType'], ['username', 'mobile'])) {
            $userIds = $this->searchUserIds($query);
            if (empty($userIds)) {
                return [true, []];
            }
            $conditions['userIds'] = $userIds;
        }
        if ('goodsName' == $query['keywordType']) {
            $goodsKeys = $this->searchGoodsKeys($query, $goodsTypes);
            if (empty($goodsKeys)) {
                return [true, []];
            }
            $conditions['goodsKeys'] = $goodsKeys;
        }
        if (!empty($conditions['goodsKeys'])) {
            unset($conditions['goodsType']);
        }

        return [false, $conditions];
    }

    private function searchUserIds($query)
    {
        if ('username' == $query['keywordType']) {
            $conditions = ['nickname' => $query['keyword']];
        }
        if ('mobile' == $query['keywordType']) {
            $conditions = ['verifiedMobile' => $query['keyword']];
        }
        if (empty($conditions)) {
            return [];
        }
        $users = $this->getUserService()->searchUsers($conditions, [], 0, PHP_INT_MAX, ['id']);

        return array_column($users, 'id');
    }

    private function searchGoodsKeys($query, $goodsTypes)
    {
        $goodsKeys = [];
        $goodsTypes = empty($query['goodsType']) ? $goodsTypes : [$query['goodsType']];
        foreach ($goodsTypes as $goodsType) {
            if ('course' == $goodsType) {
                $courses = $this->getCourseService()->searchCourses(['courseOrCourseSetTitleLike' => $query['keyword']], [], 0, PHP_INT_MAX, ['id']);
                foreach ($courses as $course) {
                    $goodsKeys[] = "{$goodsType}_{$course['id']}";
                }
            }
            if ('classroom' == $goodsType) {
                $classrooms = $this->getClassroomService()->searchClassrooms(['titleLike' => $query['keyword']], [], 0, PHP_INT_MAX, ['id']);
                foreach ($classrooms as $classroom) {
                    $goodsKeys[] = "{$goodsType}_{$classroom['id']}";
                }
            }
        }

        return $goodsKeys;
    }

    private function wrap($signedContracts)
    {
        $users = $this->getUserService()->findUsersByIds(array_column($signedContracts, 'userId'));

        return $signedContracts;
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
