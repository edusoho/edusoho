<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\EncryptionToolkit;
use Biz\Common\CommonException;
use Biz\RewardPoint\AccountException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Pay\Service\AccountService;

class MePayPassword extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $passwords = $request->request->all();
        $host = $request->getHttpRequest()->getHost();
        $user = $this->getCurrentUser();

        if ($this->getAccountService()->isPayPasswordSetted($user['id'])) {
            throw AccountException::PAY_PASSWORD_EXISTED();
        }

        if (!ArrayToolkit::requireds($passwords, array('loginPassword', 'payPassword', 'confirmPayPassword'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $passwords = $this->decryptPasswords($passwords, $host);
        $this->checkPayPasswords($passwords['payPassword'], $passwords['confirmPayPassword']);

        if (!$this->getUserService()->verifyPassword($user['id'], $passwords['loginPassword'])) {
            throw UserException::PASSWORD_FAILED();
        }

        $this->getAccountService()->setPayPassword($user['id'], $passwords['payPassword']);

        return array(
            'success' => true,
        );
    }

    public function update(ApiRequest $request, $client)
    {
        $passwords = $request->request->all();
        $host = $request->getHttpRequest()->getHost();
        $user = $this->getCurrentUser();

        if (!$this->getAccountService()->isPayPasswordSetted($user['id'])) {
            throw AccountException::NOTFOUND_PAY_PASSWORD();
        }

        if (!ArrayToolkit::requireds($passwords, array('oldPayPassword', 'newPayPassword', 'confirmPayPassword'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $passwords = $this->decryptPasswords($passwords, $host);
        $this->checkPayPasswords($passwords['newPayPassword'], $passwords['confirmPayPassword']);

        if (!$this->getAccountService()->validatePayPassword($user['id'], $passwords['oldPayPassword'])) {
            throw AccountException::ERROR_PAY_PASSWORD();
        }

        $this->getAccountService()->setPayPassword($user['id'], $passwords['newPayPassword']);

        return array(
            'success' => true,
        );
    }

    private function checkPayPasswords($payPassword, $confirmPayPassword)
    {
        if ($payPassword != $confirmPayPassword) {
            throw AccountException::ERROR_PAY_PASSWORD_FORMAT();
        }

        if (strlen($payPassword) > 20) {
            throw AccountException::ERROR_PAY_PASSWORD_FORMAT();
        }
    }

    private function decryptPasswords($passwords, $key)
    {
        foreach ($passwords as $index => $password) {
            $passwords[$index] = EncryptionToolkit::XXTEADecrypt(base64_decode($password), $key);
        }

        return $passwords;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}
