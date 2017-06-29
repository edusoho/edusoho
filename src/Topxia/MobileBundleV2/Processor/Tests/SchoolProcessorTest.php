<?php

namespace Topxia\MobileBundleV2\Processor\Tests;

class SchoolProcessorTest extends BaseProcessorTest
{
    protected $service = 'School';

    public function testGetSchoolSite()
    {
        $requestData = array();
        $jsonData = $this->getContent($requestData, $this->service, __FUNCTION__, 'GET');

        $this->assertNotNull($jsonData->site);
        $this->assertNotNull($jsonData->site->name);
        $this->assertNotNull($jsonData->site->url);
        $this->assertNotNull($jsonData->site->host);
        $this->assertNotNull($jsonData->site->apiVersionRange);
    }

    public function testGetSchoolBanner()
    {
        $requestData = array();
        $jsonData = $this->getContent($requestData, $this->service, __FUNCTION__, 'GET');

        $this->assertNotNull($jsonData);
    }
}
