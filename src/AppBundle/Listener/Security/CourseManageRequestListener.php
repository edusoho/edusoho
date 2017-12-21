<?php

namespace AppBundle\Listener\Security;

use ApiBundle\ApiBundle;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class CourseManageRequestListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (preg_match('^/course/(\d+)/manage', $request->getPathInfo(), $matches)) {
            try {
                $this->getCourseService()->tryManageCourse($matches[1][0]);
            } catch (\Exception $e) {
                throw new AccessDeniedHttpException('Forbidden', $e);
            }
        }
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    private function getCourseService()
    {
        return $this->container->get('biz')->service('Course:CourseService');
    }
}
