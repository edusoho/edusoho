<?php
namespace Topxia\Service\MoneyCard;

interface MoneyCardService
{
    /**
    * 输入数组
    * key1: @money 充值的金额
    * key2: @cardPrefix  帐号的前缀
    * key3: @cardLength  帐号的长度
    * key4: @number  生成本批次帐号的数目
    * key5: @note 本次充值的备注
    * key6: @deadline 截止失效日期
    * key7: @passwordLength 密码长度
    *  
    * 返回值 array() 
    * 本批次充值生成的记录
    **/

    public function createMoneyCard (array $moneyCardData);

	public function getMoneyCard ($id);

    public function getBatch ($id);

	public function searchMoneyCards (array $conditions, array $oderBy, $start, $limit);

    public function searchMoneyCardsCount(array $conditions);

    public function searchBatchs(array $conditions, array $oderBy, $start, $limit);

    public function searchBatchsCount(array $conditions);

    public function lockMoneyCard ($id);

    public function unlockMoneyCard ($id);

    public function deleteMoneyCard ($id);

    public function lockBatch ($id);

    public function unlockBatch ($id);

    public function deleteBatch ($id);

}