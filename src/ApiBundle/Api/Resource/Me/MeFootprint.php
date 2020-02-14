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
        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => $request->query->get('type'),
        );
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getUserFootprintService()->countUserFootprints($conditions);
        $footprints = $this->getUserFootprintService()->searchUserFootprints($conditions, array('updatedTime' => 'DESC'), $offset, $limit);

        $footprints = $this->getUserFootprintService()->prepareUserFootprintsByType($footprints, $request->query->get('type'));

        return $this->makePagingObject($footprints, $total, $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        $footprint = $request->request->all();

        if (!ArrayToolkit::requireds($footprint, array('targetType', 'targetId', 'event'))) {
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
