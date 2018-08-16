<?php

namespace ApiBundle\Api\Resource\ActivityResource;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use ApiBundle\Api\Annotation\Access;

class ActivityResource extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param $resourceId
     *
     * @return array
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function get(ApiRequest $request, $resourceId)
    {
        $params = $request->query->all();

        if (!ArrayToolkit::requireds($params, array('resourceType'))) {
            throw new InvalidParameterException('param is wrong!');
        }

        $activityConfig = $this->getActivityConfig($params['resourceType']);

        return $activityConfig->get($resourceId);
    }

    /**
     * @param ApiRequest $request
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (!ArrayToolkit::requireds($params, array('resourceType'))) {
            throw new InvalidParameterException('param is wrong!');
        }

        $activityConfig = $this->getActivityConfig($params['resourceType']);

        return $activityConfig->create($params);
    }

    /**
     * @param ApiRequest $request
     * @param $resourceId
     * @Access(roles="ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function update(ApiRequest $request, $resourceId)
    {
        $params = $request->request->all();

        if (!ArrayToolkit::requireds($params, array('resourceType'))) {
            throw new InvalidParameterException('param is wrong!');
        }

        $activityConfig = $this->getActivityConfig($params['resourceType']);

        return $activityConfig->update($resourceId, $params);
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
