<?php


namespace Codeages\PluginBundle\Event;


use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class CacheDispatcher extends EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function dispatch($eventName, Event $event = null)
    {
        if(empty($this->getListeners())){
            $this->container->get('codeages_plugin.event.subscribers');
            $biz = $this->container->get('biz');
            foreach ($biz['subscribers'] as $subscriber){
                $this->addSubscriber(new $subscriber);
            }
        }

        return parent::dispatch($eventName, $event);
    }
}