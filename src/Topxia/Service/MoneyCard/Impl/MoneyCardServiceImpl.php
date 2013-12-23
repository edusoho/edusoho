<?php
namespace Topxia\Service\MoneyCard\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;

class MoneyCardServiceImpl extends BaseService
{
	public function getMoneyCard ($id)
    {
		return $this->getMoneyCardDao()->getMoneyCard($id);
	}

    public function getBatch ($id)
    {
        return $this->getMoneyCardBatchDao()->getBatch($id);
    }

	public function searchMoneyCards (array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getMoneyCardDao()->searchMoneyCards($conditions, $oderBy, $start, $limit);
    }

    public function searchMoneyCardsCount(array $conditions)
    {
        return $this->getMoneyCardDao()->searchMoneyCardsCount($conditions);
    }

    public function searchBatchs(array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getMoneyCardBatchDao()->searchBatchs($conditions, $oderBy, $start, $limit);
    }

    public function searchBatchsCount(array $conditions)
    {
        return $this->getMoneyCardBatchDao()->searchBatchsCount($conditions);
    }

    public function createMoneyCard (array $moneyCardData)
    {
        $batch = ArrayToolkit::parts($moneyCardData, array(
            'money',
            'cardPrefix',
            'cardLength',
            'number',
            'note',
            'deadline'
        ));

        $batch['money'] = (int)$batch['money'];
        $batch['cardLength'] = (int)$batch['cardLength'];
        $batch['number'] = (int)$batch['number'];

        if ($batch['money'] <= 0) {
            throw $this->createServiceException('ERROR! Money Value Less Than Zero!');
        }
        if ($batch['cardLength'] <= 0) {
            throw $this->createServiceException('ERROR! CardLength Less Than Zero!');
        }
        if ($batch['number'] <= 0) {
            throw $this->createServiceException('ERROR! Card Number Less Than Zero!');
        }

        $batch['rechargedNumber'] = 0;
        $batch['userId'] = $this->getCurrentUser()->id;
        $batch['createdTime'] = time();

        $moneyCardIds = $this->makeRands($batch['cardLength'], $batch['number'], $batch['cardPrefix'], $moneyCardData['passwordLength']);

        if (!$this->getMoneyCardDao()->isCardIdAvaliable($moneyCardIds)) {
            throw $this->createServiceException('卡号有重复，生成失败，请重新生成！');
        }

        $batch = $this->getMoneyCardBatchDao()->addBatch($batch);

        $moneyCards = array();
        foreach ($moneyCardIds as $cardid => $cardPassword) {
            $moneyCards[] = array(
                'cardId' => $cardid,
                'password' => $cardPassword,
                'deadline' => $moneyCardData['deadline'],
                'cardStatus' => 'normal',
                'batchId' => $batch['id']
            );
        }

        $this->getMoneyCardDao()->addMoneyCard($moneyCards);
        $this->getLogService()->info('money_card_batch', 'create', "创建新批次充值卡,卡号前缀为({$batch['cardPrefix']}),批次为({$batch['id']})");
        return $batch;
    }

    public function lockMoneyCard ($id)
    {
        $moneyCard = $this->getMoneyCard($id);
        if (empty($moneyCard)) {
            throw $this->createServiceException('充值卡不存在，作废失败！');
        }
        if ($moneyCard['cardStatus'] == 'normal') {
            $moneyCard = $this->getMoneyCardDao()->updateMoneyCard($moneyCard['id'], array('cardStatus' => 'invalid'));

            $this->getLogService()->info('money_card', 'lock', "作废了卡号为{$moneyCard['cardId']}的充值卡");
        } else {
            throw $this->createServiceException('只能作废正常状态的充值卡！');
        }
        return $moneyCard;
    }

    public function unlockMoneyCard ($id)
    {
        $moneyCard = $this->getMoneyCard($id);
        if (empty($moneyCard)) {
            throw $this->createServiceException('充值卡不存在，作废失败！');
        }
        if ($moneyCard['cardStatus'] == 'invalid') {
            $moneyCard = $this->getMoneyCardDao()->updateMoneyCard($moneyCard['id'], array('cardStatus' => 'normal'));

            $this->getLogService()->info('money_card', 'unlock', "启用了卡号为{$moneyCard['cardId']}的充值卡");
        } else {
            throw $this->createServiceException('只能启用作废状态的充值卡！');
        }
        return $moneyCard;
    }

    public function deleteMoneyCard ($id)
    {
        $moneyCard = $this->getMoneyCard($id);
        if ($moneyCard['cardStatus'] != 'recharged') {
            $this->getMoneyCardDao()->deleteMoneyCard($id);

            $this->getLogService()->info('money_card', 'delete', "删除了卡号为{$moneyCard['cardId']}的充值卡");
        } else {
            throw $this->createServiceException('不能删除已经充值的充值卡！');
        }
    }

    public function lockBatch ($id)
    {
        $batch = $this->getBatch($id);
        if (empty($batch)) {
            throw $this->createServiceException('批次不存在，作废失败！');
        }
        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
            'batchId' => $batch['id'],
            'cardStatus' => 'normal'
            ),
            array('cardStatus' => 'invalid')
        );

        $this->getLogService()->info('money_card_batch', 'lock', "作废了批次为{$batch['id']}的充值卡");

        return $batch;
    }

    public function unlockBatch ($id)
    {
        $batch = $this->getBatch($id);
        if (empty($batch)) {
            throw $this->createServiceException('批次不存在，作废失败！');
        }
        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
            'batchId'    => $batch['id'],
            'cardStatus' => 'invalid'
            ),
            array('cardStatus' => 'normal')
        );

        $this->getLogService()->info('money_card_batch', 'unlock', "启用了批次为{$batch['id']}的充值卡");

        return $batch;
    }

    public function deleteBatch ($id)
    {
        $this->getMoneyCardBatchDao()->deleteBatch($id);
        $this->getMoneyCardDao()->deleteBatchByCardStatus(array($id, 'recharged'));

        $this->getLogService()->info('money_card_batch', 'delete', "删除了批次为{$id}的充值卡");
    }

    private function makeRands ($median, $number, $cardPrefix, $passwordLength)
    {
        $cardIds = array();
        $i = 0;
        while(true) {
            $id = '';
            for ($j=0; $j < (int)$median; $j++) {
                $id .= mt_rand(0, 9);
            }

            if (!isset($cardIds[$cardPrefix.$id])) {
                $cardIds[$cardPrefix.$id] = $this->makePassword($passwordLength);
                $i++;
            }
            if ($i >= $number) {
                break;
            }
        }
        return $cardIds;
    }

    private function makePassword ($length)
    {
        $password = '';
        for ($j=0; $j < (int)$length; $j++) {
                $password .= mt_rand(0, 9);
            }

        return $password;
    }


    private function getMoneyCardDao()
    {
        return $this->createDao('MoneyCard.MoneyCardDao');
    }

    private function getMoneyCardBatchDao()
    {
        return $this->createDao('MoneyCard.MoneyCardBatchDao');
    }

    protected function getLogService ()
    {
        return $this->createService('System.LogService');
    }
}