<?php

namespace ApiBundle\Api\Resource\SearchKeyword;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class SearchKeyword extends AbstractResource
{
    const MAX_KEYWORD_NUM = 8;

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search()
    {
        $keywords = $this->getSearchKeywordService()->searchSearchKeywords(array(), array('times' => 'DESC'), 0, self::MAX_KEYWORD_NUM);

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

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->service('SearchKeyword:SearchKeywordService');
    }
}
