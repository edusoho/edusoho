<?php

namespace ApiBundle\Api\Resource\SearchKeyword;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class SearchKeyword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $name = $request->query->get('name');
        $limit = $request->query->get('limit');

        $keywords = $this->getSearchKeywordService()->searchSearchKeywords(array('likeName' => $name), array('times' => 'DESC'), 0, $limit);
        $keywords = ($keywords) ? $this->filterKeyword($keywords) : array();

        return $keywords;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $name = $request->request->get('name');

        $keyword = $this->getSearchKeywordService()->getSearchKeywordByName($name);
        if ($keyword) {
            $this->getSearchKeywordService()->addSearchKeywordTimes($keyword['id']);
            $result = $this->getSearchKeywordService()->getSearchKeyword($keyword['id']);
        } else {
            $result = $this->getSearchKeywordService()->createSearchKeyword(array('name' => $name));
        }

        return $result;
    }

    protected function filterKeyword($keywords)
    {
        $result = array();
        foreach ($keywords as $keyword) {
            array_push($result, $keyword['name']);
        }

        return $result;
    }

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->service('SearchKeyword:SearchKeywordService');
    }
}
