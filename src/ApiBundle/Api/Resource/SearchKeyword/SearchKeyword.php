<?php

namespace ApiBundle\Api\Resource\SearchKeyword;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class SearchKeyword extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $title = $request->query->get('title');
        $type = $request->query->get('type');
        $limit = $request->query->get('limit');

        $keywords = $this->getSearchKeywordService()->searchSearchKeywords(array('likeName' => $title, 'type' => $type), array('times' => 'DESC'), 0, $limit);

        return $keywords;
    }

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->service('SearchKeyword:SearchKeywordService');
    }
}
