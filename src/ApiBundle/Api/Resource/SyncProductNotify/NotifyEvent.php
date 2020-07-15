<?php

namespace ApiBundle\Api\Resource\SyncProductNotify;

use ApiBundle\Api\Exception\ErrorCode;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

/**
 * Class NotifyEvent
 */
class NotifyEvent
{
    protected $productId;

    protected $event;

    protected $eventData;

    /**
     * NotifyEvent constructor.
     *
     * @throws ServiceException
     */
    public function __construct(array $eventData)
    {
        if (!ArrayToolkit::requireds($eventData, ['productId', 'event', 'data'])) {
            throw new ServiceException('productId,event,data is required', ErrorCode::INVALID_ARGUMENT);
        }
        $this->productId = $eventData['productId'];

        $this->event = $eventData['event'];

        $this->eventData = $eventData['data'];
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function getData($dataIndex = null)
    {
        if (is_null($dataIndex)) {
            return $this->eventData;
        }
        return $this->eventData[$dataIndex];
    }

    public function getEvent()
    {
        return $this->event;
    }
}
