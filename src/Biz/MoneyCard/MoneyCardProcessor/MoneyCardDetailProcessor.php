<?php

namespace  Biz\MoneyCard\MoneyCardProcessor;

use Biz\Card\DetailProcessor\DetailProcessor;
use Topxia\Service\Common\ServiceKernel;

class MoneyCardDetailProcessor implements DetailProcessor
{
    public function getDetailById($id)
    {
        $card = $this->getMoneyCardService()->getMoneyCard($id);
        $batch = $this->getMoneyCardService()->getBatch($card['batchId']);
        $card['coin'] = $batch['coin'];

        return $card;
    }

    public function getCardDetailsByCardIds($ids)
    {
        $moneyCards = $this->getMoneyCardService()->getMoneyCardByIds($ids);

        foreach ($moneyCards as $key => $card) {
            $batch = $this->getMoneyCardService()->getBatch($card['batchId']);
            $moneyCards[$key]['coin'] = $batch['coin'];
        }

        return $moneyCards;
    }

    protected function getMoneyCardService()
    {
        return ServiceKernel::instance()->getBiz()->service('MoneyCard:MoneyCardService');
    }
}
