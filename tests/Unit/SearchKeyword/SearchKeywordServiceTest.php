<?php

namespace Tests\Unit\SearchKeyword;

use Biz\BaseTestCase;

class SearchKeywordServiceTest extends BaseTestCase
{
    public function testCreateSearchKeyword()
    {
        $keyword = array('name' => 'test');
        $searchKeyword = $this->getSearchKeywordService()->createSearchKeyword($keyword);

        $this->assertEquals('test', $searchKeyword['name']);
    }

    public function testUpdateSearchKeyword()
    {
        $keyword = array('name' => 'test');
        $searchKeyword = $this->getSearchKeywordService()->createSearchKeyword($keyword);

        $newKeyword = $this->getSearchKeywordService()->updateSearchKeyword($searchKeyword['id'], array('name' => 'newTest'));

        $this->assertEquals('newTest', $newKeyword['name']);
    }

    public function testDeleteSearchKeyword()
    {
        $keyword = array('name' => 'test');
        $searchKeyword = $this->getSearchKeywordService()->createSearchKeyword($keyword);

        $keyword = $this->getSearchKeywordService()->deleteSearchKeyword($searchKeyword['id']);

        $this->assertEquals(1, $keyword);
    }

    public function testSearchSearchKeywords()
    {
        $keyword = array('name' => 'test');
        $this->getSearchKeywordService()->createSearchKeyword($keyword);

        $keywords = $this->getSearchKeywordService()->SearchSearchKeywords(array(), array(), 0, PHP_INT_MAX);

        $this->assertEquals(1, count($keywords));
    }

    public function testCountSearchKeyword()
    {
        $keyword = array('name' => 'test');
        $this->getSearchKeywordService()->createSearchKeyword($keyword);

        $num = $this->getSearchKeywordService()->countSearchKeywords(array());

        $this->assertEquals(1, $num);
    }

    /**
     * @return \Biz\SearchKeyword\Service\Impl\SearchKeywordServiceImpl
     */
    protected function getSearchKeywordService()
    {
        return $this->createService('SearchKeyword:SearchKeywordService');
    }
}
