<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\S2B2C\Dao\SettlementReportDao;
use Biz\S2B2C\S2B2CException;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SettlementReportService;
use Biz\User\Service\UserService;

class SettlementReportServiceImpl extends BaseService implements SettlementReportService
{
    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['s2b2cProductId', 'userId', 'type', 'orderId'])) {
            throw new \InvalidArgumentException();
        }
        $supplier = $this->getS2B2CFacadeService()->getSupplier();
        $user = $this->getUserService()->getUser($fields['userId']);

        return $this->getSettlementReportDao()->create([
            'supplierId' => $supplier['id'],
            'productId' => $fields['s2b2cProductId'],
            'type' => $fields['type'],
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
            'orderId' => $fields['orderId'],
            'status' => self::STATUS_CREATED,
            'reason' => empty($fields['reason']) ? '' : $fields['reason'],
        ]);
    }

    public function getById($id)
    {
        return $this->getSettlementReportDao()->get($id);
    }

    public function updateFailedReason($id, $reason)
    {
        $report = $this->getById($id);
        if (empty($report)) {
            $this->createNewException(S2B2CException::SETTLEMENT_REPORT_NOT_FOUND());
        }

        return $this->getSettlementReportDao()->update($id, [
            'status' => self::STATUS_FAILED,
            'reason' => $reason,
        ]);
    }

    public function updateStatusToSent($id)
    {
        $report = $this->getById($id);
        if (empty($report)) {
            $this->createNewException(S2B2CException::SETTLEMENT_REPORT_NOT_FOUND());
        }

        return $this->getSettlementReportDao()->update($id, ['status' => self::STATUS_SENT]);
    }

    public function updateStatusToSucceed($id)
    {
        $report = $this->getById($id);
        if (empty($report)) {
            $this->createNewException(S2B2CException::SETTLEMENT_REPORT_NOT_FOUND());
        }

        return $this->getSettlementReportDao()->update($id, ['status' => self::STATUS_SUCCEED]);
    }

    /**
     * @return SettlementReportDao
     */
    protected function getSettlementReportDao()
    {
        return $this->biz->dao('S2B2C:SettlementReportDao');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('S2B2C:ProductService');
    }
}