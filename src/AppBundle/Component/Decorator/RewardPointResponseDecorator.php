<?php

namespace AppBundle\Component\Decorator;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class RewardPointResponseDecorator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function decorate(Response $response)
    {
        $rewardPoint = $this->getSettingService()->get('reward_point', array());
        if (empty($rewardPoint)) {
            return;
        }

        $biz = $this->getBiz();
        $currentUser = $biz['user'];

        if ($rewardPoint['enable'] && isset($currentUser['Reward-Point-Notify'])) {
            $response->headers->set('Reward-Point-Notify', json_encode($currentUser['Reward-Point-Notify']));
        }
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return Biz
     */
    private function getBiz()
    {
        return $this->container->get('biz');
    }
}
