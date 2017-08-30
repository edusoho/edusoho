<?php

namespace Codeages\Biz\Framework\Pay\Service;

interface AccountService
{
    public function setPayPassword($userId, $password);

    public function validatePayPassword($userId, $password);

    public function setSecurityAnswers($userId, $answers);

    public function validateSecurityAnswer($userId, $questionKey, $answer);

    public function isPayPasswordSetted($userId);

    public function isSecurityAnswersSetted($userId);

    public function createUserBalance($userId);

    public function waveAmount($userId, $amount);

    public function waveCashAmount($userId, $amount);

    public function getUserBalanceByUserId($userId);
}
