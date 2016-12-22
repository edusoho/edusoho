<?php


namespace MoneyCard;


use Biz\BaseTestCase;
use Biz\MoneyCard\Service\MoneyCardService;

class MoneyCardServiceTest extends BaseTestCase
{


    public function testCreateMoneyCard()
    {
        $moneyCardData = array(
            'money'      => 22,
            'coin'       => 20,
            'cardPrefix' => 'ddd',
            'cardLength' => 15,
            'number'     => 32,
            'note'       => '',
            'deadline'   => time(),
            'batchName'  => 'dd',
            'passwordLength'=>'123456'
        );
        $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    }

//    public function getMoneyCard ($id);
//
//    public function getMoneyCardByIds($ids);
//
//    public function getBatch ($id);
//
//    public function searchMoneyCards (array $conditions, array $oderBy, $start, $limit);
//
//    public function countMoneyCards(array $conditions);
//
//
//    public function searchBatches(array $conditions, array $oderBy, $start, $limit);
//
//
//    public function countBatches(array $conditions);
//
//    public function lockMoneyCard ($id);
//
//    public function unlockMoneyCard ($id);
//
//    public function deleteMoneyCard ($id);
//
//    public function lockBatch ($id);
//
//    public function unlockBatch ($id);
//
//    public function deleteBatch ($id);

    /**
     * @return MoneyCardService
     */
    protected function getMoneyCardService()
    {
        return $this->getBiz()->service('MoneyCard:MoneyCardService');
    }

}