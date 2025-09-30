<?php

namespace ApiBundle\Api\Resource\Tag;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Taxonomy\Service\TagService;

class Tag extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getTagService()->findTagsByLikeName($request->query->get('name'));
    }

    /**
     * @return TagService
     */
    private function getTagService()
    {
        return $this->service('Taxonomy:TagService');
    }
}
