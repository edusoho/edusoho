<?php
namespace Topxia\Service\MoneyCard\Impl;

use Topxia\Service\Common\BaseService;

class MoneyCardServiceImpl extends BaseService {

	public function getMoneyCard ($id) {

		$moneyCard = $this->getMoneyCardDao()->getMoneyCard($id);
        if(!$moneyCard){
            return null;
        } else {
            return $moneyCard;
        }
	}

    public function getBatch ($id) {

        $batch = $this->getMoneyCardBatchDao()->getBatch($id);
        if(!$batch){
            return null;
        } else {
            return $batch;
        }
    }

    public function exportCsv (array $conditions, array $oderBy) {

        $conditions = array_filter($conditions);

        $moneyCards = $this->getMoneyCardDao()->searchMoneyCardsAll($conditions, $oderBy);

        $str = "卡号,密码,批次"."\r\n";

        $moneyCards = array_map(function($moneyCard){
            $card['cardId']   = $moneyCard['cardId'];
            $card['password'] = $moneyCard['password'];
            $card['batchId']  = $moneyCard['batchId'];
            return implode(',',$card);
        }, $moneyCards);

        $str .= implode("\r\n",$moneyCards);

        return array(
            'filename' => "cards-".$conditions['batchId']."-".date("YmdHi").".csv",
            'str'      => $str
            );
    }

	public function searchMoneyCards (array $conditions, array $oderBy, $start, $limit) {

		$conditions = array_filter($conditions);

        return $this->getMoneyCardDao()->searchMoneyCards($conditions, $oderBy, $start, $limit);
    }

    public function searchMoneyCardsCount(array $conditions) {

    	$conditions = array_filter($conditions);

        return $this->getMoneyCardDao()->searchMoneyCardsCount($conditions);
    }

    public function searchBatchs(array $conditions, array $oderBy, $start, $limit) {

        $conditions = array_filter($conditions);

        return $this->getMoneyCardBatchDao()->searchBatchs($conditions, $oderBy, $start, $limit);
    }

    public function searchBatchsCount(array $conditions) {

        $conditions = array_filter($conditions);

        return $this->getMoneyCardBatchDao()->searchBatchsCount($conditions);
    }

    public function createMoneyCard (array $moneyCardData) {

        $batch     = array();
        $batch['money']       = $moneyCardData['money'];
        $batch['cardPrefix']  = $moneyCardData['cardPrefix'];
        $batch['cardMedian']  = $moneyCardData['cardMedian'];
        $batch['number']      = $moneyCardData['number'];
        $batch['disc']        = $moneyCardData['disc'];
        $batch['validTime']   = $moneyCardData['validTime'];
        $batch['createdId']   = $this->getCurrentUser()->id;
        $batch['createdTime'] = time();

        $moneyCardIds = $this->makeRands($batch['cardMedian'], $batch['number'], $batch['cardPrefix'], $moneyCardData['passwordMedian']);

        $str = implode(',', array_map(function($value){ return "'".$value."'"; },
            array_keys($moneyCardIds)));
        if (!$this->getMoneyCardDao()->isCardIdAvaliable($str)) {
            throw $this->createServiceException('卡号有重复，生成失败，请重新生成！');
        }

        $batch = $this->getMoneyCardBatchDao()->addBatch($batch);


        $moneyCards = array();
        $j = 0;
        foreach ($moneyCardIds as $key => $value) {
            $moneyCards[$j++] = $key;
            $moneyCards[$j++] = $value;
            $moneyCards[$j++] = $moneyCardData['validTime'];
            $moneyCards[$j++] = "normal";
            $moneyCards[$j++] = $batch['id'];
        }

        $moneyCardsNumber = $this->getMoneyCardDao()->addMoneyCard($moneyCards, $batch['number']);

        if ($moneyCardsNumber != $batch['number']) {
            $this->getMoneyCardBatchDao()->deleteBatch($batch['id']);
            $this->getMoneyCardDao()->deleteBatch($batch['id']);

            throw $this->createServiceException('创建充值卡失败！');
        }

        return $batch;
    }

    public function isCardPrefixAvaliable ($cardPrefix) {

        if (empty($cardPrefix)) {
            return false;
        }
        $moneyCard = $this->getMoneyCardBatchDao()->findBatchByCardPrefix($cardPrefix);

        return empty($moneyCard) ? true : false;
    }

    public function lockMoneyCard ($id) {

        $moneyCard = $this->getMoneyCard($id);
        if (empty($moneyCard)) {
            throw $this->createServiceException('充值卡不存在，作废失败！');
        }
        return $this->getMoneyCardDao()->updateMoneyCard($moneyCard['id'], array('rechargeStatus' => 'invalid'));
    }

    public function unlockMoneyCard ($id) {

        $moneyCard = $this->getMoneyCard($id);
        if (empty($moneyCard)) {
            throw $this->createServiceException('充值卡不存在，作废失败！');
        }
        return $this->getMoneyCardDao()->updateMoneyCard($moneyCard['id'], array('rechargeStatus' => 'normal'));
    }

    public function deleteMoneyCard ($id) {

        return $this->getMoneyCardDao()->deleteMoneyCard($id);
    }

    public function lockBatch ($id) {

        $batch = $this->getBatch($id);
        if (empty($batch)) {
            throw $this->createServiceException('批次不存在，作废失败！');
        }
        $batch = $this->getMoneyCardBatchDao()->updateBatch($batch['id'], array('cardStatus' => 'invalid'));
        $this->getMoneyCardDao()->updateBatch($batch['id'], array('rechargeStatus' => 'invalid'));

        return $batch;
    }

    public function unlockBatch ($id) {

        $batch = $this->getBatch($id);
        if (empty($batch)) {
            throw $this->createServiceException('批次不存在，作废失败！');
        }
        $batch = $this->getMoneyCardBatchDao()->updateBatch($batch['id'], array('cardStatus' => 'normal'));
        $this->getMoneyCardDao()->updateBatch($batch['id'], array('rechargeStatus' => 'normal'));

        return $batch;
    }

    public function deleteBatch ($id) {

        $this->getMoneyCardBatchDao()->deleteBatch($id);
        $this->getMoneyCardDao()->deleteBatch($id);
    }

    private function makeRands ($median, $number, $cardPrefix, $passwordMedian) {

        $data = array();
        $cardids = array();
        $i = 0;
        while(true) {
            $id = '';
            for ($j=0; $j < (int)$median; $j++) {
                $id .= mt_rand(0, 9);
            }

            if (!in_array($id, $data)) {
                $data[] = $id;
                $cardids[$cardPrefix.$id] = $this->makePassword($passwordMedian);
                $i++;
            }
            if ($i >= $number) {
                break;
            }
        }
        return $cardids;
    }

    private function makePassword ($length) {

        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,$length);
    }


    private function getMoneyCardDao() {

        return $this->createDao('MoneyCard.MoneyCardDao');
    }

    private function getMoneyCardBatchDao() {

        return $this->createDao('MoneyCard.MoneyCardBatchDao');
    }
}