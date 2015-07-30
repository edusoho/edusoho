<?php
namespace Topxia\MobileBundleV2\Processor\Tests;

use Topxia\MobileBundleV2\Processor\Tests\BaseProcessorTest;
use Symfony\Component\HttpFoundation\Request;
use Topxia\MobileBundleV2\Controller\MobileApiController;

class SchoolProcessorTest extends BaseProcessorTest
{
	public function testGetSchoolSite()
    {
        $requestData = array(
        );

        //$uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null
        $requst = Request::create("School/getSchoolSite", "GET");
        $controller = new MobileApiController();
        $result = $controller->indexAction($requst, "School", "getSchoolSite");

        $data = $result->getContent();
        var_dump($data);
        $this->assertNotNull($data);
    }

    private function getSchoolProcessor()
    {

    }
}