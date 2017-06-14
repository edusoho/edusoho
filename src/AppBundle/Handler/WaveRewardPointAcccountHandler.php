<?php
//
//namespace AppBundle\Handler;
//
//class WaveRewardPointAcccountHandler
//{
//    /**
//     * @var ContainerInterface
//     */
//    private $container;
//
//    /**
//     * @var Biz
//     */
//    private $biz;
//
//    public function __construct(ContainerInterface $container)
//    {
//        $this->container = $container;
//        $this->biz = $this->container->get('biz');
//    }
//
//    /**
//     * Do the magic.
//     *
//     * @param InteractiveLoginEvent $event
//     */
//    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
//    {
//        $user = $this->biz['user'];
//
//
//    }
//
//    /**
//     * @return SettingService
//     */
//    protected function getSettingService()
//    {
//        return $this->biz->service('System:SettingService');
//    }
//}