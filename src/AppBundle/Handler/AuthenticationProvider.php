<?php

namespace AppBundle\Handler;

use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Topxia\Service\Common\ServiceKernel;

/**
 * 此Class大部分代码来自DaoAuthenticationProvider.
 */
class AuthenticationProvider extends UserAuthenticationProvider
{
    private $encoderFactory;
    private $userProvider;

    /**
     * Constructor.
     *
     * @param UserProviderInterface   $userProvider               An UserProviderInterface instance
     * @param UserCheckerInterface    $userChecker                An UserCheckerInterface instance
     * @param string                  $providerKey                The provider key
     * @param EncoderFactoryInterface $encoderFactory             An EncoderFactoryInterface instance
     * @param bool                    $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);

        $this->encoderFactory = $encoderFactory;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ('' === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            return $user;
        }

        try {
            if ($this->getAuthService()->hasPartnerAuth() && $this->getAuthService()->isRegisterEnabled()) {
                try {
                    $user = $this->userProvider->loadUserByUsername($username);
                    $bind = $this->getUserService()->getUserBindByTypeAndUserId($this->getAuthService()->getPartnerName(), $user['id']);

                    if ($bind) {
                        $partnerUser = $this->getAuthService()->checkPartnerLoginById($bind['fromId'], $token->getCredentials());
                        if ($partnerUser) {
                            $user = $this->syncEmailAndPassword($user, $partnerUser, $token);
                        }
                    }
                } catch (UsernameNotFoundException $notFound) {
                    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                        $partnerUser = $this->getAuthService()->checkPartnerLoginByEmail($username, $token->getCredentials());
                    } else {
                        $partnerUser = $this->getAuthService()->checkPartnerLoginByNickname($username, $token->getCredentials());
                    }

                    if (empty($partnerUser)) {
                        throw $notFound;
                    }
                    $bind = $this->getUserService()->getUserBindByTypeAndFromId($this->getAuthService()->getPartnerName(), $partnerUser['id']);
                    if ($bind) {
                        $user = $this->getUserService()->getUser($bind['toId']);
                        $user = $this->syncEmailAndPassword($user, $partnerUser, $token);
                    } else {
                        $setting = $this->getSettingService()->get('user_partner', array());
                        $emailFilter = explode("\n", $setting['email_filter']);
                        if (in_array($partnerUser['email'], $emailFilter)) {
                            $partnerUser['email'] = $partnerUser['id'].'_dz_'.$this->getRandomString(5).'@edusoho.net';
                        }
                        $registration = array();
                        $registration['nickname'] = $partnerUser['nickname'];
                        $registration['email'] = $partnerUser['email'];
                        $registration['password'] = $token->getCredentials();
                        $registration['createdIp'] = $partnerUser['createdIp'];
                        $registration['token'] = array(
                            'userId' => $partnerUser['id'],
                        );
                        $registration['type'] = $this->getAuthService()->getPartnerName();

                        $this->getUserService()->register(
                            $registration,
                            $this->getRegisterTypeToolkit()->getRegisterTypes($registration)
                        );

                        $user = $this->userProvider->loadUserByUsername($username);
                    }
                }
            } else {
                $user = $this->userProvider->loadUserByUsername($username);
            }

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }

            return $user;
        } catch (UsernameNotFoundException $notFound) {
            $notFound->setUsername($username);
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            $ex = new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
            $ex->setToken($token);
            throw $ex;
        }
    }

    protected function syncEmailAndPassword($user, $partnerUser, $token)
    {
        try {
            $isEmaildChanged = ($user['email'] != $partnerUser['email']);
            if ($isEmaildChanged) {
                $this->getUserService()->changeEmail($user['id'], $partnerUser['email']);
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'sync_email', $this->getServiceKernel()->trans('同步用户(#%userId%)Email失败', array('%userId%' => $user['id'])));
        }

        try {
            $isPasswordChanged = !$this->getUserService()->verifyPassword($user['id'], $token->getCredentials());
            if ($isPasswordChanged) {
                $this->getUserService()->changePassword($user['id'], $token->getCredentials());
            }
        } catch (\Exception $e) {
            $this->getLogService()->error('user', 'sync_password', $this->getServiceKernel()->trans('同步用户(#%userId%)密码失败', array('%userId%' => $user['id'])));
        }

        $user = $this->userProvider->loadUserByUsername($user['email']);

        return $user;
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return ServiceKernel::instance()->createService('User:AuthService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getBiz()
    {
        return $this->getServiceKernel()->getBiz();
    }

    protected function getRegisterTypeToolkit()
    {
        $biz = $this->getBiz();

        return $biz['user.register.type.toolkit'];
    }
}
