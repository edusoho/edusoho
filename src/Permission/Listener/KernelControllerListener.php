<?php
namespace Permission\Listener;

use Symfony\Component\Finder\Finder;
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
                && !in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
                $permissions = $this->getPermissionsByRoute($route);
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

    private function getPermissionsByRoute($_route)
    {
        $kernel = $this->container->get('kernel');

        if($kernel->isDebug()){
            $permissions = $this->_getPermissions($_route);
        }else{
            $permissions = $this->_getPermissionsByCache($_route);
        }

        return $permissions;
    }

    private function _getPermissionsByCache($_route)
    {
        $kernel = $this->container->get('kernel');
        $cacheFile = $kernel->getCacheDir() . '/route_permissions_meta.php';
        if(file_exists($cacheFile)){
            $permissions = include $cacheFile;
        }else{
            $finder = new Finder();
            $finder->in($kernel->getCacheDir() . '/route_permissions');
            $permissions = array();

            foreach ($finder as $file){
                $permissions = array_merge($permissions, include $file->getRealPath());
            }

            $cache = "<?php \nreturn " . var_export($permissions, true) . ';';
            file_put_contents($cacheFile, $cache);
        }

        return isset($permissions[$_route]) ? $permissions[$_route] : array();
    }

    /**
     * @param $_route
     * @return array
     */
    private function _getPermissions($_route)
    {
        return $this->container->get('router')->getRouteCollection()->get($_route)->getPermissions();
    }
}
