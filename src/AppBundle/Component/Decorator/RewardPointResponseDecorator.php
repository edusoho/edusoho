<?php

namespace AppBundle\Component\Decorator;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RewardPointResponseDecorator
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function decorate(Response $response, Request $request)
    {
        $rewardPoint = $this->getSettingService()->get('reward_point', array());
        if (empty($rewardPoint)) {
            return;
        }

        $biz = $this->getBiz();
        $currentUser = $biz['user'];

        if ($rewardPoint['enable'] && isset($currentUser['Reward-Point-Notify'])) {
            $msg = $this->transMsg($rewardPoint, $currentUser['Reward-Point-Notify']);
            $type = $request->headers->get('X-Requested-With');

            if ($type == 'XMLHttpRequest') {
                $response->headers->set('Reward-Point-Notify', rawurlencode($msg));
            } else {
                $request->getSession()->set('Reward-Point-Notify', rawurlencode($msg));
            }
        }
    }

    private function transMsg($rewardPoint, $notify)
    {
        $rewardPointName = $rewardPoint['name'];
        $amount = ($notify['type'] == 'inflow' ? '+' : '-').$notify['amount'];

        return $this->container->get('translator')->trans('reward_point.notify.'.$notify['way'], array(
            '%name%' => $rewardPointName,
            '%amount%' => $amount,
        ));
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
