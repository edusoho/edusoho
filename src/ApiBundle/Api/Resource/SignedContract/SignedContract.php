<?php

namespace ApiBundle\Api\Resource\SignedContract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Contract\Service\ContractService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\User\Service\UserService;
use Codeages\Biz\Order\Service\OrderService;

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
        $signedContract = $this->getContractService()->getSignedContract($id);
        $signSnapshot = $signedContract['snapshot'];

        return [
            'code' => $signSnapshot['contractCode'],
            'name' => $signSnapshot['contract']['name'],
            'content' => $signSnapshot['contract']['content'],
            'seal' => $signSnapshot['contract']['seal'],
            'sign' => $signSnapshot['sign'],
            'signDate' => date('Y年m月d日', $signedContract['createdTime']),
        ];
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
        $contractSnapshots = $this->getContractService()->findContractSnapshotsByIds(array_column(array_column($signedContracts, 'snapshot'), 'contractSnapshotId'));
        $contractSnapshots = array_column($contractSnapshots, null, 'id');
        $wrappedSignedContracts = [];
        foreach ($signedContracts as $signedContract) {
            list($goodsType, $targetId) = explode('_', $signedContract['goodsKey']);
            $wrappedSignedContracts[] = [
                'id' => $signedContract['id'],
                'contractCode' => $signedContract['snapshot']['contractCode'],
                'username' => $users[$signedContract['userId']]['nickname'],
                'mobile' => $users[$signedContract['userId']]['verifiedMobile'],
                'goodsType' => $goodsType,
                'goodsName' => $this->getGoodsName($goodsType, $targetId),
                'orderSn' => $this->getOrderSn($goodsType, $targetId, $signedContract['userId']),
                'contractName' => $contractSnapshots[$signedContract['snapshot']['contractSnapshotId']]['name'],
                'signTime' => $signedContract['createdTime'],
            ];
        }

        return $wrappedSignedContracts;
    }

    private function getGoodsName($goodsType, $targetId)
    {
        if ('course' == $goodsType) {
            $course = $this->getCourseService()->getCourse($targetId);

            return "{$course['courseSetTitle']}-{$course['title']}";
        }
        if ('classroom' == $goodsType) {
            $classroom = $this->getClassroomService()->getClassroom($targetId);

            return $classroom['title'];
        }
    }

    private function getOrderSn($goodsType, $targetId, $userId)
    {
        if ('course' == $goodsType) {
            $member = $this->getCourseMemberService()->getCourseMember($targetId, $userId);
        }
        if ('classroom' == $goodsType) {
            $member = $this->getClassroomService()->getClassroomMember($targetId, $userId);
        }
        if (!empty($member['orderId'])) {
            $order = $this->getOrderService()->getOrder($member['orderId']);
        }

        return $order['sn'] ?? '';
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
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }
}
