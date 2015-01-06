<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class PersonDynamicDataTag extends BaseDataTag implements DataTag  
{
    public function getData(array $arguments)
    {   
        $personDynamics=$this->getStatusService()->searchStatuses(array(), array('createdTime','DESC'), 0, $arguments['count']);
        
        $ownerIds = ArrayToolkit::column($personDynamics, 'userId');

        $owners=$this->getUserService()->findUsersByIds($ownerIds);

        foreach ($personDynamics as $key => $personDynamic) {

                $personDynamics[$key]['user'] = $owners[$personDynamic['userId']];
        }

        return $personDynamics;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
    private function getStatusService() 
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

}
