<?php

namespace Biz\SearchKeyword\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\SearchKeyword\Service\SearchKeywordService;

class SearchKeywordServiceImpl extends BaseService implements SearchKeywordService
{
    public function createSearchKeyword($keyword)
    {
        if (!ArrayToolkit::requireds($keyword, array('name'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getSearchKeywordDao()->create($keyword);
    }

    public function updateSearchKeyword($id, $keyword)
    {
        return $this->getSearchKeywordDao()->update($id, $keyword);
    }

    public function getSearchKeyword($id)
    {
        return $this->getSearchKeywordDao()->get($id);
    }

    public function getSearchKeywordByNameAndType($name, $type)
    {
        return $this->getSearchKeywordDao()->getByNameAndType($name, $type);
    }

    public function searchSearchKeywords($conditions, $orderBy, $start, $limit)
    {
        return $this->getSearchKeywordDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countSearchKeywords($conditions)
    {
        return $this->getSearchKeywordDao()->count($conditions);
    }

    public function deleteSearchKeyword($id)
    {
        return $this->getSearchKeywordDao()->delete($id);
    }

    public function addSearchKeywordTimes($id)
    {
        return $this->getSearchKeywordDao()->wave(array($id), array('times' => 1));
    }

    /**
     * @return \Biz\SearchKeyword\Dao\Impl\SearchKeywordDaoImpl
     */
    protected function getSearchKeywordDao()
    {
        return $this->createDao('SearchKeyword:SearchKeywordDao');
    }
}
