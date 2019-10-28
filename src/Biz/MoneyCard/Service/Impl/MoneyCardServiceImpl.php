<?php

namespace Biz\MoneyCard\Service\Impl;

use Biz\BaseService;
use Biz\MoneyCard\Dao\MoneyCardBatchDao;
use Biz\MoneyCard\Dao\MoneyCardDao;
use Biz\MoneyCard\MoneyCardException;
use Biz\MoneyCard\Service\MoneyCardService;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Pay\Service\AccountService;

class MoneyCardServiceImpl extends BaseService implements MoneyCardService
{
    public function getMoneyCard($id, $lock = false)
    {
        return $this->getMoneyCardDao()->get($id, array('lock' => $lock));
    }

    public function getMoneyCardByIds($ids)
    {
        return $this->getMoneyCardDao()->getMoneyCardByIds($ids);
    }

    public function getMoneyCardByPassword($password)
    {
        return $this->getMoneyCardDao()->getMoneyCardByPassword($password);
    }

    public function getBatch($id)
    {
        return $this->getMoneyCardBatchDao()->get($id);
    }

    public function getBatchByToken($token, $options = array())
    {
        return $this->getMoneyCardBatchDao()->getBatchByToken($token, $options);
    }

    public function searchMoneyCards(array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getMoneyCardDao()->search($conditions, $oderBy, $start, $limit);
    }

    public function countMoneyCards(array $conditions)
    {
        return $this->getMoneyCardDao()->count($conditions);
    }

    public function searchBatches(array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getMoneyCardBatchDao()->search($conditions, $oderBy, $start, $limit);
    }

    public function countBatches(array $conditions)
    {
        return $this->getMoneyCardBatchDao()->count($conditions);
    }

    public function createMoneyCard(array $moneyCardData)
    {
        $batch = ArrayToolkit::parts($moneyCardData, array(
            'money',
            'coin',
            'cardPrefix',
            'cardLength',
            'number',
            'note',
            'deadline',
            'batchName',
        ));

        if (isset($batch['money'])) {
            $batch['money'] = (int) $batch['money'];
        }

        if (isset($batch['coin'])) {
            $batch['coin'] = (int) $batch['coin'];
        }

        if (isset($batch['cardLength'])) {
            $batch['cardLength'] = (int) $batch['cardLength'];
        }

        if (isset($batch['number'])) {
            $batch['number'] = (int) $batch['number'];
        }

        if (isset($batch['money']) && $batch['money'] <= 0) {
            $this->createNewException(MoneyCardException::MONEY_INVALID());
        }

        if (isset($batch['coin']) && $batch['coin'] <= 0) {
            $this->createNewException(MoneyCardException::COIN_INVALID());
        }

        if (isset($batch['cardLength']) && $batch['cardLength'] <= 0) {
            $this->createNewException(MoneyCardException::CARDLENGTH_INVALID());
        }

        if (isset($batch['number']) && $batch['number'] <= 0) {
            $this->createNewException(MoneyCardException::NUMBER_INVALID());
        }

        $batch['rechargedNumber'] = 0;
        $batch['userId'] = $this->getCurrentUser()->id;
        $batch['createdTime'] = time();
        $batch['deadline'] = date('Y-m-d', strtotime($batch['deadline']));

        $moneyCardIds = $this->makeRands($batch['cardLength'], $batch['number'], $batch['cardPrefix'], $moneyCardData['passwordLength']);

        if (!$this->getMoneyCardDao()->isCardIdAvailable(array_keys($moneyCardIds))) {
            $this->createNewException(MoneyCardException::DUPLICATE_CARD());
        }

        $token = $this->getTokenService()->makeToken('money_card', array(
            'duration' => strtotime($batch['deadline']) + 24 * 60 * 60 - time(),
        ));
        $batch['token'] = $token['token'];
        $batch = $this->getMoneyCardBatchDao()->create($batch);

        foreach ($moneyCardIds as $cardid => $cardPassword) {
            $this->getMoneyCardDao()->create(
                array(
                    'cardId' => $cardid,
                    'password' => $cardPassword,
                    'deadline' => date('Y-m-d', strtotime($moneyCardData['deadline'])),
                    'cardStatus' => 'normal',
                    'batchId' => $batch['id'],
                )
            );
        }

        $this->getLogService()->info('money_card', 'batch_create', "创建新批次充值卡,卡号前缀为({$batch['cardPrefix']}),批次为({$batch['id']})");

        return $batch;
    }

    public function lockMoneyCard($id)
    {
        $moneyCard = $this->getMoneyCard($id);

        if (empty($moneyCard)) {
            $this->createNewException(MoneyCardException::NOTFOUND_MONEYCARD());
        }

        if ('normal' == $moneyCard['cardStatus'] || 'receive' == $moneyCard['cardStatus']) {
            if ('receive' == $moneyCard['cardStatus']) {
                $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

                $batch = $this->getBatch($moneyCard['batchId']);

                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'invalid'));

                $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员作废，详情请联系管理员。";

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }

            $moneyCard = $this->getMoneyCardDao()->update($moneyCard['id'], array('cardStatus' => 'invalid'));

            $this->getLogService()->info('money_card', 'lock', "作废了卡号为{$moneyCard['cardId']}的充值卡");
        } else {
            $this->createNewException(MoneyCardException::LOCK_USED_CARD());
        }

        return $moneyCard;
    }

    public function unlockMoneyCard($id)
    {
        $moneyCard = $this->getMoneyCard($id);

        if (empty($moneyCard)) {
            $this->createNewException(MoneyCardException::NOTFOUND_MONEYCARD());
        }

        $batch = $this->getBatch($moneyCard['batchId']);

        if ('invalid' == $batch['batchStatus']) {
            $this->createNewException(MoneyCardException::BATCH_STATUS_EQUAL_INVALID());
        }

        if ('invalid' == $moneyCard['cardStatus']) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'receive'));
                $this->updateMoneyCard($card['cardId'], array('cardStatus' => 'receive'));
                $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员启用。";

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            } else {
                $moneyCard = $this->getMoneyCardDao()->update($moneyCard['id'], array('cardStatus' => 'normal'));
            }

            $this->getLogService()->info('money_card', 'unlock', "启用了卡号为{$moneyCard['cardId']}的充值卡");
        } else {
            $this->createNewException(MoneyCardException::UNLOCK_NOT_INVALID_CARD());
        }

        return $moneyCard;
    }

    public function deleteMoneyCard($id)
    {
        $moneyCard = $this->getMoneyCard($id);
        $batch = $this->getBatch($moneyCard['batchId']);
        $this->getMoneyCardDao()->delete($id);
        $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');
        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'deleted'));

            $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员删除，详情请联系管理员。";

            $this->getNotificationService()->notify($card['userId'], 'default', $message);
        }

        $this->getLogService()->info('money_card', 'delete', "删除了卡号为{$moneyCard['cardId']}的充值卡");
    }

    public function lockBatch($id)
    {
        $batch = $this->getBatch($id);

        if (empty($batch)) {
            $this->createNewException(MoneyCardException::NOTFOUND_BATCH());
        }

        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
                'batchId' => $batch['id'],
                'cardStatus' => 'normal',
            ),
            array('cardStatus' => 'invalid')
        );

        $moneyCards = $this->searchMoneyCards(
            array(
                'batchId' => $batch['id'],
                'cardStatus' => 'receive',
            ),
            array('id' => 'ASC'),
            0,
            1000
        );

        foreach ($moneyCards as $moneyCard) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'invalid'));

                $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员作废，详情请联系管理员。";

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }
        }

        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
                'batchId' => $batch['id'],
                'cardStatus' => 'receive',
            ),
            array('cardStatus' => 'invalid')
        );

        $batch = $this->updateBatch($batch['id'], array('batchStatus' => 'invalid'));
        $this->getLogService()->info('money_card', 'batch_lock', "作废了批次为{$batch['id']}的充值卡");

        return $batch;
    }

    public function unlockBatch($id)
    {
        $batch = $this->getBatch($id);

        if (empty($batch)) {
            $this->createNewException(MoneyCardException::NOTFOUND_BATCH());
        }

        $moneyCards = $this->searchMoneyCards(
            array(
                'batchId' => $batch['id'],
                'cardStatus' => 'invalid',
            ),
            array('id' => 'ASC'),
            0,
            1000
        );

        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
                'batchId' => $batch['id'],
                'cardStatus' => 'invalid',
                'rechargeUserId' => 0,
            ),
            array('cardStatus' => 'normal')
        );

        foreach ($moneyCards as $moneyCard) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

            if (!empty($card) && 'invalid' == $card['status']) {
                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'receive'));
                $this->updateMoneyCard($card['cardId'], array('cardStatus' => 'receive'));
                $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员启用。";

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }
        }

        $batch = $this->updateBatch($batch['id'], array('batchStatus' => 'normal'));
        $this->getLogService()->info('money_card', 'batch_unlock', "启用了批次为{$batch['id']}的充值卡");

        return $batch;
    }

    public function deleteBatch($id)
    {
        $batch = $this->getBatch($id);

        if (empty($batch)) {
            $this->createNewException(MoneyCardException::NOTFOUND_BATCH());
        }

        $moneyCards = $this->getMoneyCardDao()->search(array('batchId' => $id), array('id' => 'ASC'), 0, 1000);

        $this->getMoneyCardBatchDao()->delete($id);
        $this->getMoneyCardDao()->deleteMoneyCardsByBatchId($id);

        foreach ($moneyCards as $moneyCard) {
            $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array('status' => 'deleted'));

                $message = "您的一张价值为{$batch['coin']}{$this->getCoinName()}的学习卡已经被管理员删除，详情请联系管理员。";

                $this->getNotificationService()->notify($card['userId'], 'default', $message);
            }
        }

        $this->getLogService()->info('money_card', 'batch_delete', "删除了批次为{$id}的充值卡");
    }

    private function getCoinName()
    {
        $coin = $this->getSettingService()->get('coin');

        return empty($coin['coin_name']) ? '虚拟币' : $coin['coin_name'];
    }

    protected function makeRands($median, $number, $cardPrefix, $passwordLength)
    {
        if ($median <= 3) {
            $this->createNewException(MoneyCardException::CARDLENGTH_INVALID());
        }

        $cardIds = array();
        $i = 0;

        while (true) {
            $id = '';

            for ($j = 0; $j < (int) $median - 3; ++$j) {
                $id .= mt_rand(0, 9);
            }

            $tmpId = $cardPrefix.$id;
            $id = $this->blendCrc32($tmpId);

            if (!isset($cardIds[$id])) {
                $tmpPassword = $this->makePassword($passwordLength);
                $cardIds[$id] = $tmpPassword;
                $this->tmpPasswords[$tmpPassword] = true;
                ++$i;
            }

            if ($i >= $number) {
                break;
            }
        }

        return $cardIds;
    }

    protected function uuid($uuidLength, $prefix = '', $needSplit = false)
    {
        $chars = md5(uniqid(mt_rand(), true));

        if ($needSplit) {
            $uuid = '';
            $uuid .= substr($chars, 0, 8).'-';
            $uuid .= substr($chars, 8, 4).'-';
            $uuid .= substr($chars, 12, 4).'-';
            $uuid .= substr($chars, 16, 4).'-';
            $uuid .= substr($chars, 20, 12);
        } else {
            $uuid = substr($chars, 0, $uuidLength);
        }
        $uuid = str_replace('i', 'a', $uuid);

        return $prefix.$uuid;
    }

    public function blendCrc32($word)
    {
        return $word.substr(crc32($word), 0, 3);
    }

    public function checkCrc32($word)
    {
        return substr(crc32(substr($word, 0, -3)), 0, 3) == substr($word, -3, 3);
    }

    private $tmpPasswords = array();

    protected function makePassword($length)
    {
        while (true) {
            $uuid = $this->uuid($length - 3);
            $password = $this->blendCrc32($uuid);
            $moneyCard = $this->getMoneyCardByPassword($password);

            if ((null == $moneyCard) && (!isset($this->tmpPasswords[$password]))) {
                break;
            }
        }

        return $password;
    }

    public function updateBatch($id, $fields)
    {
        return $this->getMoneyCardBatchDao()->update($id, $fields);
    }

    public function updateMoneyCard($id, $fields)
    {
        return $this->getMoneyCardDao()->update($id, $fields);
    }

    public function useMoneyCard($id, $fields)
    {
        try {
            $this->beginTransaction();

            $moneyCard = $this->getMoneyCard($id, true);

            if ('recharged' == $moneyCard['cardStatus']) {
                $this->rollback();

                return $moneyCard;
            }

            $moneyCard = $this->updateMoneyCard($id, $fields);

            $batch = $this->getBatch((int) $moneyCard['batchId']);

            $recharge = array(
                'to_user_id' => $fields['rechargeUserId'],
                'from_user_id' => 0,
                'amount' => $batch['coin'] * 100,
                'amount_type' => 'coin',
                'title' => '学习卡'.$moneyCard['cardId'].'充值'.$batch['coin'],
                'buyer_id' => $this->getCurrentUser()->getId(),
                'action' => 'recharge',
            );

            $this->getAccountService()->transferCoin($recharge);

            ++$batch['rechargedNumber'];
            $this->updateBatch($batch['id'], $batch);
            $card = $this->getCardService()->getCardByCardIdAndCardType($moneyCard['id'], 'moneyCard');

            if (!empty($card)) {
                $this->getCardService()->updateCardByCardIdAndCardType($moneyCard['id'], 'moneyCard', array(
                    'status' => 'used',
                    'useTime' => $moneyCard['rechargeTime'],
                ));
            } else {
                $this->getCardService()->addCard(array(
                    'cardId' => $moneyCard['id'],
                    'cardType' => 'moneyCard',
                    'status' => 'used',
                    'deadline' => strtotime($moneyCard['deadline']),
                    'useTime' => $moneyCard['rechargeTime'],
                    'userId' => $moneyCard['rechargeUserId'],
                    'createdTime' => time(),
                ));
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $moneyCard;
    }

    public function receiveMoneyCard($token, $userId)
    {
        $token = $this->getTokenService()->verifyToken('money_card', $token);

        if (!$token) {
            return array(
                'code' => 'failed',
                'message' => '无效的链接',
            );
        }

        try {
            $this->biz['db']->beginTransaction();
            $batch = $this->getMoneyCardBatchDao()->getBatchByToken($token['token'], array('lock' => 1));

            if (empty($batch)) {
                $this->biz['db']->commit();

                return array(
                    'code' => 'failed',
                    'message' => '该链接不存在或已被删除',
                );
            }

            if ('invalid' == $batch['batchStatus']) {
                $this->biz['db']->commit();

                return array(
                    'code' => 'failed',
                    'message' => '该学习卡已经作废',
                );
            }

            if (!empty($userId)) {
                $conditions = array(
                    'rechargeUserId' => $userId,
                    'batchId' => $batch['id'],
                );

                $moneyCard = $this->getMoneyCardDao()->search($conditions, array('id' => 'DESC'), 0, 1);

                if (!empty($moneyCard) && 0 == $moneyCard[0]['rechargeTime']) {
                    $this->biz['db']->commit();

                    return array(
                        'batchId' => $batch['id'],
                        'code' => 'received',
                        'message' => '您已经领取该批学习卡',
                    );
                }

                if (!empty($moneyCard) && 0 != $moneyCard[0]['rechargeTime']) {
                    $this->biz['db']->commit();

                    return array(
                        'batchId' => $batch['id'],
                        'code' => 'recharged',
                        'message' => '您已经领取并使用该批学习卡',
                    );
                }
            }

            $conditions = array(
                'rechargeUserId' => 0,
                'cardStatus' => 'normal',
                'batchId' => $batch['id'],
            );
            $moneyCards = $this->getMoneyCardDao()->search($conditions, array('id' => 'ASC'), 0, 1);

            if (empty($moneyCards)) {
                $this->biz['db']->commit();

                return array(
                    'code' => 'empty',
                    'message' => '该批学习卡已经被领完',
                );
            }

            $moneyCard = $this->getMoneyCardDao()->get($moneyCards[0]['id']);

            if (!empty($moneyCard) && !empty($userId)) {
                $moneyCard = $this->getMoneyCardDao()->update($moneyCard['id'], array(
                    'rechargeUserId' => $userId,
                    'cardStatus' => 'receive',
                    'receiveTime' => time(),
                ));

                if (empty($moneyCard)) {
                    $this->biz['db']->commit();

                    return array(
                        'code' => 'failed',
                        'message' => '学习卡领取失败',
                    );
                }

                $this->getCardService()->addCard(array(
                    'cardId' => $moneyCard['id'],
                    'cardType' => 'moneyCard',
                    'deadline' => strtotime($moneyCard['deadline']),
                    'userId' => $userId,
                ));

                $receivedNumber = $this->getMoneyCardDao()->count(array(
                    'batchId' => $batch['id'],
                    'receiveTime_GT' => 0,
                ));
                $batch = $this->getMoneyCardBatchDao()->update($batch['id'], array(
                    'receivedNumber' => $receivedNumber,
                ));

                $message = "您有一张价值为{$batch['coin']}{$this->getCoinName()}的充值卡领取成功";
                $this->getNotificationService()->notify($userId, 'default', $message);
                $this->dispatchEvent('moneyCard.receive', $batch);
            }

            $this->biz['db']->commit();

            return array(
                'id' => $moneyCard['id'],
                'batchId' => $batch['id'],
                'code' => 'success',
                'message' => '领取成功，请在卡包中查看',
            );
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    public function receiveMoneyCardByPassword($password, $userId)
    {
        $moneyCard = $this->getMoneyCardByPassword($password);
        $result = $this->canUseMoneyCard($moneyCard, $userId);
        if (!empty($result)) {
            return $result;
        }

        try {
            $this->biz['db']->beginTransaction();

            $moneyCard = $this->getMoneyCardDao()->update($moneyCard['id'], array(
                'rechargeUserId' => $userId,
                'cardStatus' => 'receive',
                'receiveTime' => time(),
            ));

            if (empty($moneyCard)) {
                $this->biz['db']->commit();

                return array(
                    'code' => 'failed',
                    'message' => 'money_card.card_receive_fail',
                );
            }

            $this->getCardService()->addCard(array(
                'cardId' => $moneyCard['id'],
                'cardType' => 'moneyCard',
                'deadline' => strtotime($moneyCard['deadline']),
                'userId' => $userId,
            ));

            $receivedNumber = $this->getMoneyCardDao()->count(array(
                'batchId' => $moneyCard['batchId'],
                'receiveTime_GT' => 0,
            ));
            $batch = $this->getMoneyCardBatchDao()->update($moneyCard['batchId'], array(
                'receivedNumber' => $receivedNumber,
            ));

            $message = $this->trans('money_card.notify.card_receive_success', array('coin_number' => $batch['coin'], 'coin_name' => $this->getCoinName()));
            $this->getNotificationService()->notify($userId, 'default', $message);
            $this->dispatchEvent('moneyCard.receive', $batch);

            $this->biz['db']->commit();

            return array(
                'id' => $moneyCard['id'],
                'code' => 'success',
                'message' => 'money_card.card_receive_success',
            );
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    protected function canUseMoneyCard($moneyCard, $userId)
    {
        if (!$moneyCard) {
            return array(
                'code' => 'failed',
                'message' => 'money_card.invalid_password',
            );
        }

        if ('invalid' == $moneyCard['cardStatus']) {
            return array(
                'code' => 'invalid',
                'message' => 'money_card.invalid_card',
            );
        }

        if ('receive' == $moneyCard['cardStatus'] && $moneyCard['rechargeUserId'] != $userId) {
            return array(
                'code' => 'receivedByOther',
                'message' => 'money_card.card_received_by_other',
            );
        }

        if ('receive' == $moneyCard['cardStatus'] && $moneyCard['rechargeUserId'] == $userId) {
            return array(
                'id' => $moneyCard['id'],
                'code' => 'received',
                'message' => 'money_card.card_received',
            );
        }

        if (0 != $moneyCard['rechargeTime'] && $moneyCard['rechargeUserId'] == $userId) {
            return array(
                'code' => 'recharged',
                'message' => 'money_card.card_used',
            );
        }

        if (0 != $moneyCard['rechargeTime'] && $moneyCard['rechargeUserId'] != $userId) {
            return array(
                'code' => 'rechargedByOther',
                'message' => 'money_card.card_used_by_other',
            );
        }

        if (!(time() < 86400 + strtotime($moneyCard['deadline']))) {
            return array(
                'code' => 'expired',
                'message' => 'money_card.expired_card',
            );
        }
    }

    /**
     * @return MoneyCardDao
     */
    protected function getMoneyCardDao()
    {
        return $this->createDao('MoneyCard:MoneyCardDao');
    }

    /**
     * @return \Biz\Card\Service\CardService
     */
    protected function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    /**
     * @return MoneyCardBatchDao
     */
    protected function getMoneyCardBatchDao()
    {
        return $this->createDao('MoneyCard:MoneyCardBatchDao');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    private function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }
}
