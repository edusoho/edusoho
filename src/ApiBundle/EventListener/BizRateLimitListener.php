<?php

namespace ApiBundle\EventListener;

use ApiBundle\Event\ResourceEvent;
use Codeages\Biz\Framework\Context\Biz;

class BizRateLimitListener
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    private $ruleMap = array(
        array('SmsCenter', 'post', 'SmsRateLimiter'),
    );

    public function handle(ResourceEvent $event)
    {
        $request = $event->getRequest();
        foreach ($this->ruleMap as $rule) {

            if ($this->isRateLimitApi($rule, $event)) {
                $rateLimiter = $this->getRateLimiter($rule[2]);
                $rateLimiter->handle($request);
            }
        }
    }

    private function isRateLimitApi($rule, ResourceEvent $event)
    {
        $request = $event->getRequest();
        $resourceProxy = $event->getResourceProxy();
        $class = get_class($resourceProxy->getResource());
        $className = $this->getClassName($class);
        return strcasecmp($rule[0], $className) === 0
            && strcasecmp($rule[1], $request->getMethod()) === 0;
    }

    private function getClassName($class)
    {
        $path = explode('\\', $class);
        return array_pop($path);
    }

    /**
     * @return \AppBundle\Component\RateLimit\RateLimiterInterface
     */
    private function getRateLimiter($name)
    {
        $limiter = null;
        switch ($name) {
            case 'SmsRateLimiter':
                $limiter = new \AppBundle\Component\RateLimit\SmsRateLimiter($this->biz);
                break;
            default:
                throw new \RuntimeException();
        }

        return $limiter;
    }
}