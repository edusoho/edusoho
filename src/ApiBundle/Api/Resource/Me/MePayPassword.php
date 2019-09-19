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
        $this->checkConfirmPayPassword($passwords['payPassword'], $passwords['confirmPayPassword']);

        $loginPassword = $this->decryptPassword($passwords['loginPassword'], $host);
        if (!$this->getUserService()->verifyPassword($user['id'], $loginPassword)) {
            throw UserException::PASSWORD_ERROR();
        }

        $payPassword = $this->decryptPassword($passwords['payPassword'], $host);
        $this->getAccountService()->setPayPassword($user['id'], $payPassword);

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
        $this->checkConfirmPayPassword($passwords['newPayPassword'], $passwords['confirmPayPassword']);

        $oldPayPassword = $this->decryptPassword($passwords['oldPayPassword'], $host);
        if (!$this->getAccountService()->validatePayPassword($user['id'], $oldPayPassword)) {
            throw AccountException::ERROR_PAY_PASSWORD();
        }

        $newPayPassword = $this->decryptPassword($passwords['newPayPassword'], $host);
        $this->getAccountService()->setPayPassword($user['id'], $newPayPassword);

        return array(
            'success' => true,
        );
    }

    private function checkConfirmPayPassword($payPassword, $confirmPayPassword)
    {
        if ($payPassword != $confirmPayPassword) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    private function decryptPassword($encryptedPassword, $key)
    {
        return EncryptionToolkit::XXTEADecrypt(base64_decode($encryptedPassword), $key);
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
