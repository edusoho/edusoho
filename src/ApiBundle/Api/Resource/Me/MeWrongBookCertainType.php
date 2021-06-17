<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\WrongBook\Service\WrongQuestionService;

class MeWrongBookCertainType extends AbstractResource
{
    public function search(ApiRequest $request, $type)
    {
        $conditions = $request->query->all();
        print_r($type);die;
        $conditions['user_id'] = $userId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        $total = $this->service('Course:MemberService')->countMembers($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFieldsGroupByTargetType(['user_id' => $userId]);
        $wrongPools = empty($wrongPools) ? 0 : ArrayToolkit::index($wrongPools, 'target_type');
        return $wrongPools;
    }

    /**

     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }
}
