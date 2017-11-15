<?php

namespace ApiBundle\Security\RateLimit;

use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Event\ResourceEvent;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimitListener
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
                $result = $rateLimiter->handle($request);

                switch ($result['code']) {
                    case RateLimiterInterface::CAPTCHA_OCCUR:

                        if ($this->isCorrectCaptcha($request)) {
                            continue;
                        } else {
                            $captcha = $this->getBizCaptcha()->generate();
                            $captchaOccurException = new CaptchaOccurHttpException(null, $result['message'], null, RateLimiterInterface::CAPTCHA_OCCUR);
                            $captchaOccurException->setData($captcha);
                            throw $captchaOccurException;
                        }
                    case RateLimiterInterface::MAX_REQUEST_OCCUR:
                        throw new TooManyRequestsHttpException(null, $result['message'], null, ErrorCode::API_TOO_MANY_CALLS);
                        break;
                    default:
                        break;

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
     * @return \ApiBundle\Security\RateLimit\RateLimiterInterface
     */
    private function getRateLimiter($name)
    {
        $class = __NAMESPACE__. '\\'. $name;
        return new $class($this->biz);
    }
}