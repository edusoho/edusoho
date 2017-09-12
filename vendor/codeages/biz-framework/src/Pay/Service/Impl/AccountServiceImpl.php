<?php

namespace Codeages\Biz\Framework\Pay\Service\Impl;

use Codeages\Biz\Framework\Pay\Dao\UserCashflowDao;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Util\RandomToolkit;
use Codeages\Biz\Framework\Pay\Service\AccountService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

class AccountServiceImpl extends BaseService implements AccountService
{
    public function setPayPassword($userId, $password)
    {
        if (empty($this->biz['user'])) {
            throw $this->createAccessDeniedException('user is not login.');
        }

        if ($userId != $this->biz['user']['id']) {
            throw $this->createAccessDeniedException('current user is invalid.');
        }

        $lock = $this->biz['lock'];
        $lock->get("set_pay_password_{$userId}");

        $account = $this->getPayAccountDao()->getByUserId($userId);

        try {
            $this->beginTransaction();
            if (empty($account)) {
                $account = $this->getPayAccountDao()->create(array(
                    'user_id' => $userId
                ));
            }

            $salt = RandomToolkit::generateString();
            $passwordEncoder = $this->getPasswordEncoder();
            $password = $passwordEncoder->encodePassword($password, $salt);

            $account = $this->getPayAccountDao()->update($account['id'], array(
                'salt' => $salt,
                'password' => $password
            ));
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release("set_pay_password_{$userId}");
            throw new ServiceException($e->getMessage());
        }

        $lock->release("set_pay_password_{$userId}");

        return $account;
    }

    public function isPayPasswordSetted($userId)
    {
        $account = $this->getPayAccountDao()->getByUserId($userId);
        return !empty($account);
    }

    public function isSecurityAnswersSetted($userId)
    {
        $savedAnswers = $this->getSecurityAnswerDao()->findByUserId($userId);
        return !empty($savedAnswers);
    }

    public function validatePayPassword($userId, $password)
    {
        $account = $this->getPayAccountDao()->getByUserId($userId);
        $passwordEncoder = $this->getPasswordEncoder();
        return $passwordEncoder->isPasswordValid($account['password'], $password, $account['salt']);
    }

    public function setSecurityAnswers($userId, $answers)
    {
        if (empty($this->biz['user'])) {
            throw $this->createAccessDeniedException('user is not login.');
        }

        if ($userId != $this->biz['user']['id']) {
            throw $this->createAccessDeniedException('current user is invalid.');
        }

        $lock = $this->biz['lock'];
        $lock->get("set_security_answers_{$userId}");

        try {
            $this->beginTransaction();

            $this->deleteAllSecurityAnswers($userId);

            foreach ($answers as $key => $answer) {
                $salt = RandomToolkit::generateString();
                $this->getSecurityAnswerDao()->create(array(
                    'user_id' => $userId,
                    'answer' => $this->getPasswordEncoder()->encodePassword($answer, $salt),
                    'salt' => $salt,
                    'question_key' => $key
                ));
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $lock->release("set_security_answers_{$userId}");
            throw new ServiceException($e);
        }

        $lock->release("set_security_answers_{$userId}");
    }

    public function findSecurityAnswersByUserId($userId)
    {
        return $this->getSecurityAnswerDao()->findByUserId($userId);
    }

    protected function deleteAllSecurityAnswers($userId)
    {
        $savedAnswers = $this->getSecurityAnswerDao()->findByUserId($userId);
        foreach ($savedAnswers as $answer) {
            $this->getSecurityAnswerDao()->delete($answer['id']);
        }
    }

    public function validateSecurityAnswer($userId, $questionKey, $answer)
    {
        if (empty($this->biz['user'])) {
            throw $this->createAccessDeniedException('user is not login.');
        }

        if ($userId != $this->biz['user']['id']) {
            throw $this->createAccessDeniedException('current user is invalid.');
        }

        $savedAnswer = $this->getSecurityAnswerDao()->getSecurityAnswerByUserIdAndQuestionKey($userId, $questionKey);
        return $this->getPasswordEncoder()->isPasswordValid($savedAnswer['answer'], $answer, $savedAnswer['salt']);
    }

    public function createUserBalance($user)
    {
        if (!ArrayToolkit::requireds($user, array('user_id'))) {
            throw $this->createInvalidArgumentException('user_id is required.');
        }

        $savedUser = $this->getUserBalanceDao()->getByUserId($user['user_id']);
        if (!empty($savedUser)) {
            return $savedUser;
        }

        $user = ArrayToolkit::parts($user, array('user_id'));
        return $this->getUserBalanceDao()->create($user);
    }

    public function getUserBalanceByUserId($userId)
    {
        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function waveAmount($userId, $amount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'amount' => $amount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function waveCashAmount($userId, $amount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'cash_amount' => $amount
        ));
        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function lockCoin($userId, $coinAmount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'amount' => (0 - $coinAmount),
            'locked_amount' => $coinAmount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function decreaseLockedCoin($userId, $amount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'locked_amount' => 0 - $amount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function releaseCoin($userId, $coinAmount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'amount' => $coinAmount,
            'locked_amount' => 0 - $coinAmount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function searchUserCashflows($conditions, $orderBy, $start, $limit)
    {
        return $this->getUserCashflowDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countUserCashflows($conditions)
    {
        return $this->getUserCashflowDao()->count($conditions);
    }

    public function sumColumnByConditions($column, $conditions)
    {
        return $this->getUserCashflowDao()->sumColumnByConditions($column, $conditions);
    }

    protected function getPasswordEncoder()
    {
        return new PasswordEncoder('sha256');
    }

    protected function getUserBalanceDao()
    {
        return $this->biz->dao('Pay:UserBalanceDao');
    }

    protected function getPayAccountDao()
    {
        return $this->biz->dao('Pay:PayAccountDao');
    }

    protected function getSecurityAnswerDao()
    {
        return $this->biz->dao('Pay:SecurityAnswerDao');
    }

    /**
     * @return UserCashflowDao
     */
    protected function getUserCashflowDao()
    {
        return $this->biz->dao('Pay:UserCashflowDao');
    }
}
