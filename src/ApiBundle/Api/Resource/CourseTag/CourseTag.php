<?php

namespace ApiBundle\Api\Resource\CourseTag;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Taxonomy\Service\TagService;

class CourseTag extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $tags = $this->getTagService()->searchTags([], ['createdTime' => 'DESC'], 0, PHP_INT_MAX);

        return ['data' => $tags];
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }
}
