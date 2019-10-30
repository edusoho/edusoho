<?php

namespace Codeages\Biz\Pay\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Pay\Util\RandomToolkit;
use Codeages\Biz\Pay\Service\AccountService;
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
            throw new ServiceException($e->getMessage());
        }

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
            throw new ServiceException($e);
        }
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

    public function searchBalances($conditions, $orderBy, $start, $limit)
    {
        return $this->getUserBalanceDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countBalances($conditions)
    {
        return $this->getUserBalanceDao()->count($conditions);
    }

    public function lockCoin($userId, $coinAmount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $userBalance = $this->getUserBalanceDao()->get($userBalance['id'], array('lock' => true));
        if (($userBalance['amount']-$coinAmount) < 0) {
            throw $this->createAccessDeniedException('coin is not enough');
        }

        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'amount' => (0 - $coinAmount),
            'locked_amount' => $coinAmount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function releaseCoin($userId, $coinAmount)
    {
        $userBalance = $this->getUserBalanceDao()->getByUserId($userId);
        $userBalance = $this->getUserBalanceDao()->get($userBalance['id'], array('lock' => true));

        $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
            'amount' => $coinAmount,
            'locked_amount' => 0 - $coinAmount
        ));

        return $this->getUserBalanceDao()->getByUserId($userId);
    }

    public function transferCash($fields)
    {
        $userFlow = array(
            'title' => $fields['title'],
            'buyer_id' => $fields['buyer_id'],
            'type' => 'outflow',
            'parent_sn' => empty($fields['parent_sn'])? '' : $fields['parent_sn'],
            'user_id' => $fields['from_user_id'],
            'trade_sn' => empty($fields['trade_sn'])? '' : $fields['trade_sn'],
            'order_sn' => empty($fields['order_sn'])? '' : $fields['order_sn'],
            'amount' => $fields['amount'],
            'action' => $fields['action'],
            'platform' => $fields['platform'],
            'currency' => $fields['currency'],
            'flow_type' => 'outflow'
        );
        $cashFlow = $this->waveCashAmount($userFlow);

        $userFlow = array(
            'title' => $fields['title'],
            'buyer_id' => $fields['buyer_id'],
            'type' => 'inflow',
            'parent_sn' => $cashFlow['sn'],
            'user_id' => $fields['to_user_id'],
            'trade_sn' => empty($fields['trade_sn'])? '' : $fields['trade_sn'],
            'order_sn' => empty($fields['order_sn'])? '' : $fields['order_sn'],
            'amount' => $fields['amount'],
            'action' => $fields['action'],
            'platform' => $fields['platform'],
            'currency' => $fields['currency'],
            'flow_type' => 'inflow'
        );
        return $this->waveCashAmount($userFlow);
    }

    public function transferCoin($fields)
    {
        $userFlow = array(
            'title' => $fields['title'],
            'buyer_id' => $fields['buyer_id'],
            'type' => 'outflow',
            'parent_sn' => empty($fields['parent_sn'])? '' : $fields['parent_sn'],
            'user_id' => $fields['from_user_id'],
            'trade_sn' => empty($fields['trade_sn'])? '' : $fields['trade_sn'],
            'order_sn' => empty($fields['order_sn'])? '' : $fields['order_sn'],
            'amount' => $fields['amount'],
            'action' => $fields['action'],
            'flow_type' => 'outflow'
        );
        $cashFlow = $this->waveCoinAmount($userFlow);

        $userFlow = array(
            'title' => $fields['title'],
            'buyer_id' => $fields['buyer_id'],
            'type' => 'inflow',
            'parent_sn' => $cashFlow['sn'],
            'user_id' => $fields['to_user_id'],
            'trade_sn' => empty($fields['trade_sn'])? '' : $fields['trade_sn'],
            'order_sn' => empty($fields['order_sn'])? '' : $fields['order_sn'],
            'amount' => $fields['amount'],
            'action' => $fields['action'],
            'flow_type' => 'inflow'
        );
        return $this->waveCoinAmount($userFlow);
    }

    public function rechargeCash($trade)
    {
        $fields = array(
            'user_id' => $trade['user_id'],
            'buyer_id' => $trade['user_id'],
            'amount' => $trade['cash_amount'],
            'title' => $trade['title'],
            'currency' => $trade['currency'],
            'platform' => $trade['platform'],
            'trade_sn' => $trade['trade_sn'],
            'order_sn' => $trade['order_sn'],
            'action' => 'recharge',
            'flow_type' => 'inflow'
        );
        return $this->waveCashAmount($fields);
    }

    public function withdrawCash($fields)
    {
        $fields['action'] = 'withdraw';
        $fields['flow_type'] = 'outflow';
        return $this->waveCashAmount($fields);
    }

    protected function waveCashAmount($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('user_id', 'amount', 'title', 'currency', 'platform'))) {
            throw $this->createInvalidArgumentException('fields is invalid.');
        }

        try {
            $this->beginTransaction();

            $amount = $fields['flow_type'] == 'inflow' ? $fields['amount'] : 0 - $fields['amount'];
            $userBalance = $this->getUserBalanceDao()->getByUserId($fields['user_id']);
            $userBalance = $this->getUserBalanceDao()->get($userBalance['id'], array('lock' => true));

            $this->getUserBalanceDao()->wave(array($userBalance['id']), array(
                'cash_amount' => $amount
            ));
            $userBalance = $this->getUserBalanceDao()->getByUserId($fields['user_id']);

            $userFlow = array(
                'sn' => $this->generateSn(),
                'title' => $fields['title'],
                'type' => $fields['flow_type'],
                'parent_sn' => empty($fields['parent_sn']) ? '' : $fields['parent_sn'],
                'currency' => $fields['currency'],
                'amount_type' => 'money',
                'user_id' => $fields['user_id'],
                'trade_sn' => empty($fields['trade_sn']) ? '' : $fields['trade_sn'],
                'order_sn' => empty($fields['order_sn']) ? '' : $fields['order_sn'],
                'platform' => empty($fields['platform']) ? '' : $fields['platform'],
                'amount' => $fields['amount'],
                'buyer_id' => $fields['buyer_id'],
                'action' => $fields['action'],
                'user_balance' => empty($userBalance['cash_amount']) ? 0 : $userBalance['cash_amount']
            );
            $cashFlow = $this->getCashflowDao()->create($userFlow);

            $this->commit();
            return $cashFlow;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function waveCoinAmount($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('user_id', 'amount', 'title'))) {
            throw $this->createInvalidArgumentException('fields is invalid.');
        }

        try {
            $this->beginTransaction();

            $userBalance = $this->getUserBalanceDao()->getByUserId($fields['user_id']);
            $userBalance = $this->getUserBalanceDao()->get($userBalance['id'], array('lock' => true));

            $userBalanceFields = array(
                'amount' => $fields['flow_type'] == 'inflow' ? $fields['amount'] : 0 - $fields['amount']
            );
            $userBalanceFields = $this->filterUserBalanceFields($fields['action'], $fields['flow_type'], $userBalanceFields, $userBalance);
            $this->getUserBalanceDao()->wave(array($userBalance['id']), $userBalanceFields);
            $userBalance = $this->getUserBalanceDao()->getByUserId($fields['user_id']);

            $userFlow = array(
                'sn' => $this->generateSn(),
                'title' => $fields['title'],
                'type' => $fields['flow_type'],
                'parent_sn' => empty($fields['parent_sn']) ? '' : $fields['parent_sn'],
                'currency' => 'coin',
                'amount_type' => 'coin',
                'user_id' => $fields['user_id'],
                'trade_sn' => empty($fields['trade_sn']) ? '' : $fields['trade_sn'],
                'order_sn' => empty($fields['order_sn']) ? '' : $fields['order_sn'],
                'platform' => 'none',
                'amount' => $fields['amount'],
                'buyer_id' => $fields['buyer_id'],
                'action' => $fields['action'],
                'user_balance' => empty($userBalance['amount']) ? 0 : $userBalance['amount']
            );
            $cashFlow = $this->getCashflowDao()->create($userFlow);

            $this->commit();
            return $cashFlow;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function filterUserBalanceFields($action, $flowType, $fields, $userBalance)
    {
        if ($action == 'recharge') {
            if ($flowType == 'outflow') {
                $fields['purchase_amount'] = abs($fields['amount']);
            } elseif ($flowType == 'inflow') {
                $fields['recharge_amount'] = abs($fields['amount']);
            }
        }

        if ($action == 'purchase') {
            if ($flowType == 'outflow') {
                $fields['purchase_amount'] = abs($fields['amount']);
            } elseif ($flowType == 'inflow') {
                $fields['recharge_amount'] = abs($fields['amount']);
            }
        }
        if ($action == 'refund') {

            if ($flowType == 'outflow') {
                $fields['recharge_amount'] = 0 - abs($fields['amount']);
            } elseif ($flowType == 'inflow') {
                $fields['purchase_amount'] = 0 - abs($fields['amount']);
            }
        }
        return $fields;
    }

    public function sumAmountGroupByUserId($conditions)
    {
        $result = $this->getCashflowDao()->sumAmountGroupByUserId($conditions);
        
        return ArrayToolkit::index($result, 'user_id');
    }

    protected function generateSn($prefix = '')
    {
        return $prefix.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function searchCashflows($conditions, $orderBy, $start, $limit, $columns = array())
    {
        return $this->getCashflowDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function countCashflows($conditions)
    {
        return $this->getCashflowDao()->count($conditions);
    }

    public function sumColumnByConditions($column, $conditions)
    {
        return $this->getCashflowDao()->sumColumnByConditions($column, $conditions);
    }

    public function countUsersByConditions($conditions)
    {
        return $this->getCashflowDao()->countUsersByConditions($conditions);
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

    protected function getCashflowDao()
    {
        return $this->biz->dao('Pay:CashflowDao');
    }
}
