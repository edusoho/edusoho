<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\InvalidCredentialException;
use ApiBundle\Api\Resource\ResourceManager;
use ApiBundle\Api\Resource\ResourceProxy;
use ApiBundle\Api\Util\ExceptionUtil;
use ApiBundle\ApiBundle;
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
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $biz = $this->container->get('biz');

        $biz['user'] = $currentUser;

        return $currentUser;
    }

    private function batchRequest(Request $request)
    {
        $batchArgsRaw = $request->request->get('batch');

        if (!$batchArgsRaw) {
            throw new InvalidArgumentException('缺少参数');
        }

        $jsonRequests = json_decode($batchArgsRaw, true);
        $this->validateJsonRequests($jsonRequests);

        $result = array();
        foreach ($jsonRequests as $jsonRequest)
        {
            $components = parse_url($jsonRequest['relative_url']);
            $body = array();
            if (!empty($jsonRequest['body'])) {
                parse_str($jsonRequest['body'], $body);
            }

            $apiRequest = new ApiRequest(
                ApiBundle::API_PREFIX.$components['path'],
                $jsonRequest['method'],
                $components['query'],
                $body
            );

            try {
                $successResponse = $this->handleApiRequest($apiRequest);
                $result[] = array(
                    'code' => 200,
                    'body' => $successResponse
                );
            } catch (\Exception $e) {
                list($error, $httpCode) = ExceptionUtil::getErrorAndHttpCodeFromException($e, $this->isDebug());
                $result[] = array(
                    'code' => $httpCode,
                    'body' => $error
                );
            }

        }

        return $result;
    }

    private function isDebug()
    {
        $env = $this->container->get( 'kernel' )->getEnvironment();
        return $env == 'dev';
    }

    private function validateJsonRequests($jsonRequests)
    {
        foreach ($jsonRequests as $jsonRequest)
        {
            if (empty($jsonRequest['method'] || empty($jsonRequest['relative_url']))) {
                throw new InvalidArgumentException('batch参数不正确');
            }
        }
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
            ApiConf::class
        );

        if ($annotation && !$annotation->getIsRequiredAuth()) {
            return;
        }

        $currentUser = $this->biz['user'];

        if ($currentUser->isLogin()) {
            return;
        }

        throw new InvalidCredentialException('token不存在或者token已经失效');
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