<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\User\Service\UserFootprintService;

class MeFootprint extends AbstractResource
{
    private $supportTypes = array('task');

    public function search(ApiRequest $request)
    {
        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => $request->query->get('type', 'task'),
        );
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $total = $this->getUserFootprintService()->countUserFootprints($conditions);
        $footprints = $this->getUserFootprintService()->searchUserFootprints($conditions, array('updatedTime' => 'DESC'), $offset, $limit);

        $type = $this->filterType($request->query->get('type', 'task'));

        $footprints = $this->getUserFootprintService()->prepareUserFootprintsByType($footprints, $type);

        return $this->makePagingObject($footprints, $total, $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        $footprint = $request->request->all();

        if (!ArrayToolkit::requireds($footprint, array('targetType', 'targetId', 'event'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $footprint = $this->getUserFootprintService()->createUserFootprint($footprint);

        return array('result' => !empty($footprint));
    }

    private function filterType($type)
    {
        if (!in_array($type, $this->supportTypes)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return ucfirst($type);
    }

    /**
     * @return UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->service('User:UserFootprintService');
    }
}
