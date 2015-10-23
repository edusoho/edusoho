<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LocationController extends BaseController
{
    public function allAction(Request $request)
    {

    	$locations = $this->getLocationService()->getAllLocations();

    	$data = array();
    	foreach ($locations as $location) {
    		$data[$location['id']] = array($location['name'], $location['parentId']);
    	}

    	return $this->createJsonmResponse($data);
    }

    protected function getLocationService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.LocationService');
    }

}
