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
        $conditions['user_id'] = $this->getCurrentUser()->getId();
        $conditions['target_type'] = $type;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('WrongBook:WrongQuestionService')->searchWrongQuestion(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );
        print_r($members);die;
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
