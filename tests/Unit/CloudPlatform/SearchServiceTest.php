<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\SearchService;

class SearchServiceTest extends BaseTestCase
{
    public function testNotifyDelete()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getSearchService()->setCloudApi('leaf', $mockObject);

        $result = $this->getSearchService()->notifyDelete(array('category' => 'test', 'id' => 1));
        $this->assertEquals(array('success' => true), $result);
    }

    public function testNotifyUpdate()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $this->getSearchService()->setCloudApi('leaf', $mockObject);

        $result = $this->getSearchService()->notifyUpdate(array('category' => 'test', 'id' => 1));
        $this->assertEquals(array('success' => true), $result);
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->createService('CloudPlatform:SearchService');
    }
}
