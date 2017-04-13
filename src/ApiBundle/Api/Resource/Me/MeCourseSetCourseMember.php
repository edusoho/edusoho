<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Annotation\ApiConf;
use AppBundle\Common\ArrayToolkit;

class MeCourseSetCourseMember extends Resource
{
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new ApiNotFoundException('课程不存在');
        }

        $conditions['courseSetId'] = $courseSetId;
        $conditions['userId'] = $this->getCurrentUser()->getId();

        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $this->getOCUtil()->multiple($members, array('userId'));

        return $members;
    }

}