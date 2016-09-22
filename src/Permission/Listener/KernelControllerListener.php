<?php
namespace Permission\Listener;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class KernelControllerListener
{
    protected $paths;

    public function __construct(Container $container, $paths)
    {
        $this->container = $container;
        $this->paths     = $paths;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request     = $event->getRequest();
        $route       = $request->attributes->get('_route');
        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $requestPath = $request->getPathInfo();

        foreach ($this->paths as $key => $path) {
            $needJudgePermission = preg_match($path, $requestPath);

            if ($needJudgePermission
                && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])
            ) {
                $route = $this->container
                    ->get('router')
                    ->getMatcher()
                    ->match($request->getPathInfo());

                $permissions = empty($route['_permission']) ? array() : $route['_permission'];

                if (empty($permissions)) {
                    return;
                }

                foreach ($permissions as $permission) {
                    if ($currentUser->hasPermission($permission)) {
                        return;
                    }
                }

                $self = $this;
                $event->setController(function () use ($self, $request) {
                    return $self->container->get('templating')->renderResponse('PermissionBundle:Admin:permission-error.html.twig');
                });
            }
        }
    }
}
