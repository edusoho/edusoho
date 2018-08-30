<?php

namespace Codeages\Biz\Pay\Service;

use Biz\System\Annotation\Log;

interface AccountService
{
    /**
     * @param $userId
     * @param $password
     *
     * @return mixed
     * @Log(module="user",action="pay-password-changed",serviceName="User:UserService",funcName="getUser",param="userId")
     */
    public function setPayPassword($userId, $password);

    public function validatePayPassword($userId, $password);

    /**
     * @param $userId
     * @param $answers
     *
     * @return mixed
     * @Log(module="user",action="password-security-answers",serviceName="User:UserService",funcName="getUser",param="userId")
     */
    public function setSecurityAnswers($userId, $answers);

    public function findSecurityAnswersByUserId($userId);

    public function validateSecurityAnswer($userId, $questionKey, $answer);

    public function isPayPasswordSetted($userId);

    public function isSecurityAnswersSetted($userId);

    public function createUserBalance($userId);

    public function getUserBalanceByUserId($userId);

    public function countBalances($conditions);

    public function searchBalances($conditions, $orderBy, $start, $limit);

    public function lockCoin($userId, $coinAmount);

    public function releaseCoin($userId, $coinAmount);

    public function transferCoin($fields);

    public function transferCash($fields);

    public function rechargeCash($trade);

    public function withdrawCash($fields);

    public function countCashflows($conditions);

    public function searchCashflows($conditions, $orderBy, $start, $limit);

    public function sumColumnByConditions($column, $conditions);

    public function countUsersByConditions($conditions);

    public function sumAmountGroupByUserId($conditions);
}
