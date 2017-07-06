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
            'course_members' => $namespace.'\\Resource\\CourseMembers',
            'course_upload_file' => $namespace.'\\Resource\\CourseUploadFile',
        );

        if (!isset($classMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Provider not available: %s', $type));
        }

        return $classMap[$type];
    }

    /**
     * @param [type] $type
     *
     * @return \AppBundle\Controller\Callback\CourseLive\BaseProvider
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
        $providerType = $request->query->get('provider');
        $provider = $this->getProvider($providerType);

        if ($request->isMethod('POST')) {
            return new JsonResponse($provider->post($request));
        } else {
            return new JsonResponse($provider->get($request));
        }
    }
}
