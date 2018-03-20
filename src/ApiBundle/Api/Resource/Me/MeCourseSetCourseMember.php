<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MeCourseSetCourseMember extends AbstractResource
{
    public function search(ApiRequest $request, $courseSetId)
    {
        $courseSet = $this->service('Course:CourseSetService')->getCourseSet($courseSetId);

        if (!$courseSet) {
            throw new NotFoundHttpException('课程不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
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
