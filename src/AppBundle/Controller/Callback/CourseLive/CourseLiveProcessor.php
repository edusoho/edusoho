<?php

namespace AppBundle\Controller\Callback\CourseLive;

use Codeages\Biz\Framework\Context\BizAware;
use AppBundle\Controller\Callback\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CourseLiveProcessor extends BizAware implements ProcessorInterface
{
    private $pool = array();

    public function getProviderClassMap($type)
    {
        $namespace = __NAMESPACE__;

        $classMap = array(
            'course_cloud_files' => $namespace.'\\Resource\\CourseCloudFiles',
            'course_member' => $namespace.'\\Resource\\CourseMember',
        );

        if (!isset($classMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Provider not available: %s', $type));
        }

        return $classMap[$type];
    }

    /**
     * @param [type] $type
     *
     * @return \AppBundle\Controller\Callback\CloudSearch\BaseProvider
     */
    public function getProvider($type)
    {
        if (empty($this->pool[$type])) {
            $class = $this->getProviderClassMap($type);
            $instance = new $class();
            $instance->setBiz($this->biz);
            $this->pool[$type] = $instance;
        }

        return $this->pool[$type];
    }

    public function execute(Request $request)
    {
        $method = strtolower($request->getMethod());
        if ($method != 'get') {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }

        $providerType = $request->query->get('provider');
        $provider = $this->getProvider($providerType);

        return new JsonResponse($provider->get($request));
    }
}
