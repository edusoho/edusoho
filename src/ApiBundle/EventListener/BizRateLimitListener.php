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

    //namespace '//' replace to '_'
    private $ruleMap = array(
        array('ApiBundle_Api_Resource_SmsCenter_SmsCenter', 'post', 'register_sms_rate_limiter'),
        array('ApiBundle_Api_Resource_User_UserSmsResetPassword', 'post', 'sms_rate_limiter'),
        array('ApiBundle_Api_Resource_Login_Login', 'post', 'sms_rate_limiter'),
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

        return 0 === strcasecmp($rule[0], $className)
            && 0 === strcasecmp($rule[1], $request->getMethod());
    }

    private function getClassName($class)
    {
        $classStr = str_replace('\\', '_', $class);

        return $classStr;
    }

    /**
     * @return \AppBundle\Component\RateLimit\RateLimiterInterface
     */
    private function getRateLimiter($name)
    {
        return $this->biz[$name];
    }
}
