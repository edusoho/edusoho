<?php

namespace ApiBundle\EventListener;

use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Event\ResourceEvent;
use AppBundle\Component\RateLimit\RateLimiterInterface;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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

                try {
                    $rateLimiter->handle($request);
                } catch (TooManyRequestsHttpException $exception) {
                    $isPassVerifyCaptcha = $exception->getCode() === RateLimiterInterface::CAPTCHA_OCCUR
                        && $this->isCorrectCaptcha($request);

                    if (!$isPassVerifyCaptcha) {
                        throw $exception;
                    }
                }
            }
        }
    }

    /**
     * @return \Biz\Common\BizCaptcha
     */
    private function getBizCaptcha()
    {
        return $this->biz['biz_captcha'];
    }

    private function isCorrectCaptcha(Request $request)
    {
        $phrase = $request->request->get('phrase', '');
        $captchaId = $request->request->get('captchaToken', '');

        return $this->getBizCaptcha()->check($captchaId, $phrase);
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
        $class = __NAMESPACE__. '\\'. $name;
        return new $class($this->biz);
    }
}