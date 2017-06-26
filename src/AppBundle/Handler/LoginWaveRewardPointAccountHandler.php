<?php

namespace AppBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginWaveRewardPointAccountHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $this->biz['user'];
        $params = array(
            'userId' => $user['id'],
            'way' => 'daily_login',
            'targetId' => $user['id'],
            'targetType' => 'daily_login',
        );

        $commonAcquireRewardPoint = $this->getRewardPointFactory('common-acquire');
        $commonAcquireRewardPoint->reward($params);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getRewardPointFactory($type)
    {
        $biz = $this->biz;
        if (!isset($biz["reward_point.{$type}"])) {
            return null;
        }

        return $biz["reward_point.{$type}"];
    }
}
