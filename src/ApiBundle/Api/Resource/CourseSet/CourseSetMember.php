<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\Resource;

class CourseSetMember extends Resource
{
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