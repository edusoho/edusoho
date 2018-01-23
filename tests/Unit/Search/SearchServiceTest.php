<?php

namespace Tests\Unit\Search;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Search\Service\SearchService;

class SearchServiceTest extends BaseTestCase
{
    public function testCloudSearch()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('success' => true, 'body' => array(
            'datas' => array('1' => array('articleId' => 1, 'updatedTime' => '1234567890', 'content' => 'test', 'category' => 'test')),
            'count' => 1,
        )));
        $this->getSearchService()->setCloudApi('leaf', $mockObject);

        $result = $this->getSearchService()->cloudSearch('article', array('type' => 'article', 'word' => '123', 'page' => 10));

        $this->assertEquals(1, count($result[0]));
        $this->assertEquals(1, $result[1]);
    }

    public function testCloudSearchWithCourse()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('success' => true, 'body' => array(
            'datas' => array(),
            'count' => 0,
        )));
        $this->getSearchService()->setCloudApi('leaf', $mockObject);

        $result = $this->getSearchService()->cloudSearch('course', array('type' => 'course', 'word' => '123', 'page' => 10));

        $this->assertEquals(0, count($result[0]));
        $this->assertEquals(0, $result[1]);
    }

    public function testRefactorAllDocuments()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getSearchService()->setCloudApi('root', $mockObject);

        $result = $this->getSearchService()->refactorAllDocuments();

        $this->assertEquals(array('success' => true), $result);
    }

    public function testApplySearchAccount()
    {
        $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array('url' => 'http://www.test.com')),
            array('functionName' => 'set', 'returnValue' => array('success' => true)),
        ));
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getSearchService()->setCloudApi('root', $mockObject);

        $result = $this->getSearchService()->applySearchAccount('/test');
        $this->assertTrue($result);
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->createService('Search:SearchService');
    }
}
