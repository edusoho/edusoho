<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseSetMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        $conditions = $request->query->all();
        $conditions['courseSetId'] = $courseSetId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->service('Course:MemberService')->countMembers($conditions);

        $this->getOCUtil()->multiple($members, array('userId'));

        return $this->makePagingObject($members, $total, $offset, $limit);
    }
}
