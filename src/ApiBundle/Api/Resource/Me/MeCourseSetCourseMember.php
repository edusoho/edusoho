<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseSetException;

class MeCourseSetCourseMember extends AbstractResource
{
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw CourseSetException::NOTFOUND_COURSESET();
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
