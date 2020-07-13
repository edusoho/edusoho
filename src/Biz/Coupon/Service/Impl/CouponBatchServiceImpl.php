<?php

namespace Biz\Coupon\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Coupon\Dao\CouponBatchDao;
use Biz\Coupon\Service\CouponBatchService;
use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Topxia\Service\Common\ServiceKernel;

class CouponBatchServiceImpl extends BaseService implements CouponBatchService
{
    public function getBatch($id)
    {
        return $this->getCouponBatchDao()->get($id);
    }

    public function findBatchsByIds(array $ids)
    {
        $batchs = $this->getCouponBatchDao()->findBatchsByIds($ids);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function getBatchByToken($token, $locked = false)
    {
        return $this->getCouponBatchDao()->getBatchByToken($token, $locked);
    }

    public function updateUnreceivedNumByBatchId($batchId)
    {
        $batch = $this->getBatch($batchId);
        if (empty($batch)) {
            throw $this->createServiceException('优惠券批次不存在');
        }

        $unreceivedNum = $this->getCouponService()->searchCouponsCount([
            'userId' => 0,
            'batchId' => $batch['id'],
        ]);

        $this->getCouponBatchDao()->update($batchId, ['unreceivedNum' => $unreceivedNum]);
        $this->dispatchEvent('coupon.receive', $batch);
    }

    public function generateCoupon($couponData)
    {
        $couponData = array_filter($couponData);
        $couponData['h5MpsEnable'] = isset($couponData['channel']['h5MpsEnable']) ?: 0;
        $couponData['linkEnable'] = isset($couponData['channel']['linkEnable']) ?: 0;
        $couponData['codeEnable'] = isset($couponData['channel']['codeEnable']) ?: 0;
        $couponData['deadline'] = isset($couponData['deadline']) ? strtotime($couponData['deadline']) : 0;
        $couponData['fixedDay'] = isset($couponData['fixedDay']) ? $couponData['fixedDay'] : 0;

        $batchArray = [
            'name',
            'prefix',
            'type',
            'rate',
            'generatedNum',
            'digits',
            'deadlineMode',
            'deadline',
            'fixedDay',
            'targetType',
            'h5MpsEnable',
            'linkEnable',
            'codeEnable', ];
        if (!ArrayToolkit::requireds($couponData, $batchArray)) {
            throw $this->createServiceException('缺少必要参数，生成优惠码失败');
        }

        $batch = ArrayToolkit::parts($couponData, $batchArray);
        if (0 == $batch['h5MpsEnable'] && 0 == $batch['linkEnable'] && 0 == $batch['codeEnable']) {
            throw $this->createServiceException('至少选择一个发放渠道');
        }

        $batch['createdTime'] = time();
        $batch['unreceivedNum'] = $batch['generatedNum'];

        if ('fullDiscount' == $couponData['targetType']) {
            if (!isset($couponData['fullDiscountPrice'])) {
                throw $this->createServiceException('缺少必要参数，生成优惠码失败');
            }

            if (!$couponData['fullDiscountPrice'] > 0) {
                throw $this->createServiceException('满减条件金额必须大于0');
            }

            $batch['fullDiscountPrice'] = $couponData['fullDiscountPrice'];
        }

        if (isset($couponData['targetId'])) {
            $batch['targetId'] = $couponData['targetId'];
        }

        if (isset($couponData['description'])) {
            $batch['description'] = $couponData['description'];
        }

        if (('time' == $batch['deadlineMode']) && ($batch['deadline'] + 86400 < $batch['createdTime'])) {
            throw $this->createServiceException('优惠码有效期不能比当前日期晚！');
        }

        $duration = ('time' == $batch['deadlineMode']) ? $batch['deadline'] + 86400 - time() : 3600;
        $token = $this->getTokenService()->makeToken('coupon', [
            'duration' => $duration,
        ]);

        $batch['token'] = $token['token'];

        try {
            $batch = $this->getCouponBatchDao()->create($batch);
        } catch (\Exception $e) {
            throw $this->createServiceException($e->getMessage());
        }

        return $batch;
    }

    public function searchBatchsCount(array $conditions)
    {
        return $this->getCouponBatchDao()->count($conditions);
    }

    public function searchBatchs(array $conditions, $orderBy, $start, $limit)
    {
        $batchs = $this->getCouponBatchDao()->search($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($batchs, 'id');
    }

    public function deleteBatch($id)
    {
        if (empty($id)) {
            throw $this->createServiceException(sprintf('优惠码批次不存在或已被删除'));
        }

        $coupons = $this->getCouponService()->findCouponsByBatchId($id, 0, 1000);

        $this->getCouponService()->deleteCouponsByBatch($id);
        $this->getCouponBatchDao()->delete($id);

        foreach ($coupons as $coupon) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', ['status' => 'deleted']);

                if ('minus' == $coupon['type']) {
                    $message = '您的一张价值为￥'.$coupon['rate'].'的优惠券已经被管理员删除，详情请联系管理员。';
                } else {
                    $message = '您的一张折扣为'.$coupon['rate'].'折的优惠券已经被管理员删除，详情请联系管理员。';
                }

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }
        }
    }

    public function checkBatchPrefix($prefix)
    {
        if (empty($prefix)) {
            return false;
        }

        $prefix = $this->getCouponBatchDao()->findBatchByPrefix($prefix);

        return empty($prefix) ? true : false;
    }

    public function receiveCoupon($token, $userId, $canRepeat = false)
    {
        $batch = $this->getCouponBatchDao()->getBatchByToken($token, true);
        $token = $this->getTokenService()->verifyToken('coupon', $token);

        if (!$token && ('time' == $batch['deadlineMode'])) {
            return [
                'code' => 'failed',
                'message' => '无效的链接',
                'exception' => [
                    'class' => 'Biz\Coupon\CouponException',
                    'method' => 'INVALID',
                ],
            ];
        }
        $user = $this->getUserService()->getUser($userId);
        if (!$user) {
            return [
                'code' => 'failed',
                'message' => '请登录网校账号后再领取',
                'exception' => [
                    'class' => 'Biz\User\UserException',
                    'method' => 'UN_LOGIN',
                ],
            ];
        }
        try {
            if (empty($batch)) {
                return [
                    'code' => 'failed',
                    'message' => '链接不存在或已被删除',
                    'exception' => [
                        'class' => 'Biz\Coupon\CouponException',
                        'method' => 'INVALID',
                    ],
                ];
            }

            $conditions = [
                'userId' => $userId,
                'batchId' => $batch['id'],
            ];
            $coupon = $this->getCouponService()->searchCoupons($conditions, ['id' => 'DESC'], 0, 1);

            if (!empty($coupon) && !$canRepeat) {
                return [
                    'code' => 'failed',
                    'message' => '您已经领取该批优惠码',
                    'exception' => [
                        'class' => 'Biz\Coupon\CouponException',
                        'method' => 'RECEIVED',
                    ],
                ];
            }

            $conditions = [
                'userId' => 0,
                'batchId' => $batch['id'],
            ];
            $coupons = $this->getCouponService()->searchCoupons($conditions, ['id' => 'ASC'], 0, 1);

            if (empty($coupons)) {
                return [
                    'code' => 'failed',
                    'message' => '该批优惠码已经被领完',
                    'exception' => [
                        'class' => 'Biz\Coupon\CouponException',
                        'method' => 'FINISHED',
                    ],
                ];
            }

            $this->getLock()->get("receive_coupon_{$batch['id']}", 10);
            $couponsId = ArrayToolkit::column($coupons, 'id');
            $coupon = $this->getCouponService()->getCoupon($couponsId[0]);

            if (!empty($userId) && !empty($coupon)) {
                $fields = [
                    'userId' => $userId,
                    'status' => 'receive',
                    'receiveTime' => time(),
                ];

                if ('day' == $batch['deadlineMode']) {
                    if (0 == $batch['fixedDay']) {
                        return [
                            'code' => 'failed',
                            'message' => '优惠码领取后过期日期为0',
                            'exception' => [
                                'class' => 'Biz\Coupon\CouponException',
                                'method' => 'INVALID',
                            ],
                        ];
                    }

                    //ES优惠券领取时，对于优惠券过期时间会加86400秒，所以计算deadline时对于固定天数模式应与设置有效期模式一致，都为当天凌晨00:00:00
                    $fields['deadline'] = strtotime(date('Y-m-d')) + 24 * 60 * 60 * $batch['fixedDay'];
                }
                $this->getCouponBatchDao()->db()->beginTransaction();
                $coupon = $this->getCouponService()->updateCoupon($coupon['id'], $fields);

                if (empty($coupon)) {
                    $this->getCouponBatchDao()->db()->commit();
                    $this->getLock()->release("receive_coupon_{$batch['id']}");

                    return [
                        'code' => 'failed',
                        'message' => '优惠码领取失败',
                        'exception' => [
                            'class' => 'Biz\Coupon\CouponException',
                            'method' => 'RECEIVE_FAILED',
                        ],
                    ];
                }

                $this->getCardService()->addCard([
                    'cardType' => 'coupon',
                    'cardId' => $coupon['id'],
                    'deadline' => $coupon['deadline'],
                    'userId' => $userId,
                ]);

                if ('minus' == $coupon['type']) {
                    $message = '您有一张价值'.$coupon['rate'].'元的优惠券领取成功';
                } else {
                    $message = '您有一张抵扣为'.$coupon['rate'].'折的优惠券领取成功';
                }

                $this->getNotificationService()->notify($userId, 'default', $message);
                $this->getLogService()->info('coupon', 'receive', "领取了优惠券 {$coupon['code']}", $coupon);
            }

            $this->updateUnreceivedNumByBatchId($batch['id']);
            $this->getCouponBatchDao()->db()->commit();
            $this->getLock()->release("receive_coupon_{$batch['id']}");

            return [
                'id' => $coupon['id'],
                'code' => 'success',
                'message' => '领取成功，请在卡包中查看',
            ];
        } catch (\Exception $e) {
            $this->getCouponBatchDao()->db()->rollback();
            $this->getLock()->release("receive_coupon_{$batch['id']}");

            throw $e;
        }
    }

    public function updateBatch($id, $fields)
    {
        return $this->getCouponBatchDao()->update($id, $fields);
    }

    public function sumDeductAmountByBatchId($batchId)
    {
        return $this->getCouponBatchDao()->sumDeductAmountByBatchId($batchId);
    }

    public function createBatchCoupons($batchId, $generatedNum = 0)
    {
        $batch = $this->getBatch($batchId);
        if (empty($batch)) {
            return null;
        }

        $generated = $this->getCouponService()->searchCouponsCount(['batchId' => $batch['id']]);
        $remain = intval($batch['generatedNum'] - $generated);
        if ($remain < $generatedNum) {
            $generatedNum = $remain;
        }

        $couponDao = $this->getCouponDao();
        $batchCreateHelper = $this->getBatchCreateHelper($couponDao);
        try {
            $time = time();
            for ($i = 0; $i < $generatedNum; ++$i) {
                $couponCode = $this->generateRandomCode($batch['digits'], $batch['prefix']);

                $coupon = [
                    'code' => $couponCode,
                    'type' => $batch['type'],
                    'status' => 'unused',
                    'rate' => $batch['rate'],
                    'batchId' => $batch['id'],
                    'deadline' => ('time' == $batch['deadlineMode']) ? $batch['deadline'] : 0,
                    'targetType' => $batch['targetType'],
                    'targetId' => $batch['targetId'],
                    'targetIds' => $batch['targetIds'],
                    'fullDiscountPrice' => $batch['fullDiscountPrice'],
                    'createdTime' => $time,
                ];
                $batchCreateHelper->add($coupon);
            }
            $batchCreateHelper->flush();
        } catch (\Exception $e) {
            throw $this->createServiceException($e->getMessage());
        }

        return true;
    }

    public function searchH5MpsBatches($conditions, $offset, $limit)
    {
        $this->fullSearchH5MpsBatchesConditions($conditions);

        $batches = $this->getCouponBatchDao()->searchH5MpsBatches($conditions, $offset, $limit);

        return ArrayToolkit::index($batches, 'id');
    }

    public function fillUserCurrentCouponByBatches($batches)
    {
        $batches = ArrayToolkit::index($batches, 'id');
        $user = $this->getCurrentUser();
        if (!empty($user['id']) && !empty($batches)) {
            $conditions = [
                'batchIds' => array_keys($batches),
                'userId' => $user['id'],
            ];

            $receivedCoupons = $this->getCouponService()->searchCoupons(
                $conditions,
                [],
                0,
                $this->getCouponService()->searchCouponsCount($conditions)
            );
            foreach ($receivedCoupons as $coupon) {
                //如果当前批次已有且当前循环的优惠券已使用时不做覆盖
                if (!empty($batches[$coupon['batchId']]['currentUserCoupon']) && 'receive' != $coupon['status']) {
                    continue;
                }
                $batches[$coupon['batchId']]['currentUserCoupon'] = $coupon;
            }
        }

        return $batches;
    }

    public function countH5MpsBatches($conditions)
    {
        $this->fullSearchH5MpsBatchesConditions($conditions);

        return $this->getCouponBatchDao()->countH5MpsBatches($conditions);
    }

    public function getCouponBatchContent($batchId)
    {
        $batch = $this->getBatch($batchId);
        $wrapper = $this->getWrapper();
        $batch = $wrapper->handle($batch, 'CouponBatch');

        return $batch['targetContent'];
    }

    public function getCouponBatchTargetDetail($batchId)
    {
        $batch = $this->getBatch($batchId);
        $wrapper = $this->getWrapper();
        $batch = $wrapper->handle($batch, 'CouponBatch');

        return $batch['targetDetail'];
    }

    //为了兼容接口老版本存在target，需要将targetIds中的结果进行转换
    public function getTargetByBatchId($batchId)
    {
        $batch = $this->getBatch($batchId);
        if (empty($batch)) {
            return null;
        }
        if (empty($batch['targetType']) || empty($batch['targetId']) || 'all' == $batch['targetType']) {
            return null;
        }
        if ('vip' != $batch['targetType'] && empty($batch['targetIds'])) {
            return null;
        }
        $targetId = current($batch['targetIds']);
        switch ($batch['targetType']) {
            case 'course':
                $target = $this->getCourseSetService()->getCourseSet($targetId);
                break;

            case 'vip':
                if ($this->isPluginInstalled('Vip')) {
                    //vip业务没有修改，沿用原来的id
                    $target = $this->getLevelService()->getLevel($batch['targetId']);
                } else {
                    $target = null;
                }
                break;

            case 'classroom':
                $target = $this->getClassroomService()->getClassroom($targetId);
                break;

            default:
                break;
        }

        return $target;
    }

    protected function fullSearchH5MpsBatchesConditions(&$conditions)
    {
        $user = $this->getCurrentUser();

        if (!ArrayToolkit::requireds($conditions, ['targetType', 'targetId'])) {
            throw $this->createInvalidArgumentException('Lack of required fields.');
        }

        $conditions = ArrayToolkit::parts($conditions, ['targetType', 'targetId']);

        $conditions['likeTargetIds'] = "%|{$conditions['targetId']}|%";
        $conditions['deadlineGt'] = time() - 86400;
        unset($conditions['targetId']);
        if (!empty($user['id'])) {
            $conditions['userId'] = $user['id'];
        }
    }

    private function generateRandomCode($length, $prefix)
    {
        $randomCode = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $code = substr($randomCode, 0, $length);

        $code = $prefix.strtoupper($code);

        $existsCode = $this->getCouponService()->getCouponByCode($code);

        if ($existsCode) {
            return $this->generateRandomCode($length, $prefix);
        } else {
            return $code;
        }
    }

    private function getBatchCreateHelper($dao)
    {
        return new BatchCreateHelper($dao);
    }

    private function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return CouponBatchDao
     */
    private function getCouponBatchDao()
    {
        return $this->createDao('Coupon:CouponBatchDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getWrapper()
    {
        global $kernel;

        return $kernel->getContainer()->get('web.wrapper');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function isPluginInstalled($code)
    {
        $pluginManager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($code);
    }
}
