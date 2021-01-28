<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class CourseSetLatestMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseSetId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $members = $this->service('Course:MemberService')->findLatestStudentsByCourseSetId($courseSetId, $offset, $limit);

        $this->getOCUtil()->multiple($members, array('userId'));

        return $members;
    }
}
