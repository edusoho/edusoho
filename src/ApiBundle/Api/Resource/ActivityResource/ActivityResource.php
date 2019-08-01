<?php

namespace ApiBundle\Api\Resource\ActivityResource;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;

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
            throw CommonException::ERROR_PARAMETER_MISSING();
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
            throw CommonException::ERROR_PARAMETER_MISSING();
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

        if (!ArrayToolkit::requireds($params, array('resourceType', 'activityId'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $activity = $this->getActivityService()->getActivity($params['activityId']);
        if ($activity['mediaId'] != $resourceId) {
            throw ActivityException::ACTIVITY_NOT_MATCH_MEDIA();
        }

        $activityConfig = $this->getActivityConfig($params['resourceType']);

        return $activityConfig->update($resourceId, $params, $activity);
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
