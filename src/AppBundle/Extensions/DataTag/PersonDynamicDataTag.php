<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class PersonDynamicDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取个人动态
     *
     *   count    必需
     *
     * @param array $arguments 参数
     *
     * @return array 个人动态
     */
    public function getData(array $arguments)
    {
        $personDynamics = $this->getStatusService()->searchStatuses(
            array('private' => 0),
            array('createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        $ownerIds = ArrayToolkit::column($personDynamics, 'userId');

        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        foreach ($personDynamics as $key => $personDynamic) {
            $personDynamics[$key]['user'] = $owners[$personDynamic['userId']];
        }

        return $personDynamics;
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function getStatusService()
    {
        return $this->createService('User:StatusService');
    }
}
