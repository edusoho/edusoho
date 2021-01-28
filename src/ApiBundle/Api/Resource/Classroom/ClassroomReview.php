<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class ClassroomReview extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $classroomId)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        return $this->invokeResource(new ApiRequest(
            '/api/review',
            'GET',
            array_merge($request->query->all(), [
                'classroomId' => $classroomId,
                'parentId' => 0,
                'targetId' => $classroomId,
                'targetType' => 'classroom',
                'offset' => $offset,
                'limit' => $limit,
                'needPosts' => true,
            ])
        ));
    }
}
