<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\Exception\InvalidCredentialException;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\Resource\ResourceProxy;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\TokenService;
use Codeages\Biz\Framework\Context\Biz;
use Doctrine\Common\Annotations\CachedReader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ResourceKernel
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CachedReader
     */
    private $annotationReader;

    /**
     * @var Biz
     */
    private $biz;

    /**
     * @var PathParser
     */
    private $pathParser;

    /**
     * @var ResourceManager
     */
    private $resManager;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->annotationReader = $container->get('annotation_reader');
        $this->biz = $container->get('biz');
        $this->pathParser = $this->biz['api.path.parser'];
        $this->resManager = $this->biz['api.resource.manager'];
    }

    public function handle(Request $request)
    {
        $this->loadUserFromToken($request->headers->get('X-Auth-Token'));

        if ($request->getPathInfo() == '/api/batch') {
            return $this->batchRequest($request);
        } else {
            return $this->singleRequest($request);
        }
    }

    private function loadUserFromToken($token)
    {
        $dbToken = $this->biz->service('User:UserService')->getToken(TokenService::TYPE_API_AUTH, $token);

        if ($dbToken) {
            $user = $this->biz->service('User:UserService')->getUser($dbToken['userId']);
            $this->setCurrentUser($user);
        }
    }

    private function setCurrentUser($user)
    {
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = array(
                'id' => 0,
                'nickname' => '游客',
                'email' => 'fakeUser',
                'locale' => 'zh_CN'
            );
        }

        $user['currentIp'] = $this->container->get('request')->getClientIp();
        $user['roles'][] = 'API_USER';
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $biz = $this->container->get('biz');
        $biz['user'] = $currentUser;

        return $currentUser;
    }

    private function batchRequest(Request $request)
    {
        $apiRequest = new ApiRequest($request->getPathInfo(), $request->getMethod(), $request->query, $request->request);
        return $this->handleApiRequest($apiRequest);
    }

    private function singleRequest(Request $request)
    {
        $apiRequest = new ApiRequest($request->getPathInfo(), $request->getMethod(), $request->query, $request->request);
        return $this->handleApiRequest($apiRequest);
    }

    private function handleApiRequest(ApiRequest $apiRequest)
    {
        $pathMeta = $this->pathParser->parse($apiRequest);
        $resourceProxy = $this->resManager->create($pathMeta);

        $this->checkResourcePermission($resourceProxy, $pathMeta->getResMethod());

        return $this->invoke($apiRequest, $resourceProxy, $pathMeta);
    }

    private function checkResourcePermission(ResourceProxy $resourceProxy, $method)
    {
        $annotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod(get_class($resourceProxy->getResource()), $method),
            'ApiBundle\Api\Annotation\ApiConf'
        );

        if ($annotation && !$annotation->getIsRequiredAuth()) {
            return;
        }

        $currentUser = $this->biz['user'];

        if ($currentUser->isLogin()) {
            return;
        }

        throw new InvalidCredentialException('需要登陆才能访问接口');
    }

    private function invoke($apiRequest, $resource, PathMeta $pathMeta)
    {
        $resMethod = $pathMeta->getResMethod();

        if (!is_callable(array($resource, $resMethod))) {
            throw new ApiNotFoundException('Method does not exist');
        }

        $params = array_merge(array($apiRequest), $pathMeta->getSlugs());
        return call_user_func_array(array($resource, $resMethod), $params);
    }
}