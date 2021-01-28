<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\User\Service\UserFootprintService;

class MeFootprint extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
        ];
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getUserFootprintService()->countUserFootprints($conditions);
        $footprints = $this->getUserFootprintService()->searchUserFootprints($conditions, ['updatedTime' => 'DESC'], $offset, $limit);
        $footprints = $this->warpperFootprints($footprints);

        return $this->makePagingObject($footprints, $total, $offset, $limit);
    }

    protected function warpperFootprints($footprints)
    {
        $warpperFootprints = [];

        $footprintGroups = ArrayToolkit::group($footprints, 'targetType');
        foreach ($footprintGroups as $targetType => $footprints) {
            $warpperFootprints = array_merge($warpperFootprints, $this->getUserFootprintService()->prepareUserFootprintsByType($footprints, $targetType));
        }

        return ArrayToolkit::sortPerArrayValue($warpperFootprints, 'updatedTime', false);
    }

    public function add(ApiRequest $request)
    {
        $footprint = $request->request->all();

        if (!ArrayToolkit::requireds($footprint, ['targetType', 'targetId', 'event'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $footprint['userId'] = $this->getCurrentUser()->getId();

        $footprint = $this->getUserFootprintService()->createUserFootprint($footprint);

        return $footprint;
    }

    /**
     * @return UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->service('User:UserFootprintService');
    }
}
