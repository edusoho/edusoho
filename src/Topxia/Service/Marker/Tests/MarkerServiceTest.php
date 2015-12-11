<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class MarkerServiceTest extends BaseTestCase
{
    public function testAddMarker()
    {
        $this->getMarkerService()->addMarker();
    }

    public function testGetMarker()
    {
    }

    public function testGetMarkersByIds()
    {
    }

    public function testSearchMarkers()
    {
    }

    public function testUpdateMarker()
    {
    }

    public function testDeleteMarker()
    {
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }
}
