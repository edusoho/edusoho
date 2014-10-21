<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;


class NavigationDataTag extends BaseDataTag implements DataTag  
{

	public function getData(array $arguments)
	{
		return $this->getNavigationService()->getNavigationsListByType($arguments['type']);
	}


	protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

}
