<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class MarkerServiceTest extends BaseTestCase
{
    public function testAddMarker()
    {
        $fields = array(
            'second' => 30
        );
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker['mediaId'], 1);
        $this->assertEquals($marker['second'], 30);
        return $marker;
    }

    public function testGetMarker()
    {
        $fields = array(
            'second' => 30
        );
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $marker = $this->getMarkerService()->getMarker($marker['id']);
        $this->assertEquals($marker['mediaId'], 1);
        $this->assertEquals($marker['second'], 30);
        return $marker;
    }

    public function testGetMarkersByIds()
    {
        $fields = array(
            'second' => 30
        );
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker2 = $this->getMarkerService()->addMarker(3, $fields);
        $markers = $this->getMarkerService()->getMarkersByIds(array(1, 2));
        $this->assertEquals($markers[1]['mediaId'], 1);
        $this->assertEquals($markers[2]['mediaId'], 3);
        return $markers;
    }

    public function testSearchMarkers()
    {
        $fields = array(
            'second' => 30
        );
        $marker1    = $this->getMarkerService()->addMarker(1, $fields);
        $marker2    = $this->getMarkerService()->addMarker(3, $fields);
        $marker3    = $this->getMarkerService()->addMarker(3, $fields);
        $conditions = array(
            'mediaId' => 3
        );
        $markers = $this->getMarkerService()->searchMarkers($conditions, array('createdTime', 'DESC'), 0, 10);
        $this->assertEquals($markers[0]['mediaId'], 3);
        return $markers;
    }

    public function testUpdateMarker()
    {
        $fields = array(
            'second' => 30
        );
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker1['second'], 30);
        $fields = array(
            'second'      => 20,
            'updatedTime' => time()
        );
        $marker2 = $this->getMarkerService()->updateMarker($marker1['id'], $fields);
        $this->assertEquals($marker2['second'], 20);
        return $marker2;

    }

    public function testDeleteMarker()
    {
        $fields = array(
            'second' => 30
        );
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker1['second'], 30);
        $marker = $this->getMarkerService()->deleteMarker($marker1['id']);
        $this->assertEquals($marker, true);
        return $marker;
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}
