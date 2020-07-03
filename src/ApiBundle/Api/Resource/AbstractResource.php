<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Util\ObjectCombinationUtil;
use Biz\Common\CommonException;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractResource
{
    /**
     * @var Biz
     */
    protected $biz;

    protected $container;

    const METHOD_SEARCH = 'search';
    const METHOD_GET = 'get';
    const METHOD_ADD = 'add';
    const METHOD_REMOVE = 'remove';
    const METHOD_UPDATE = 'update';

    const DEFAULT_PAGING_LIMIT = 10;
    const DEFAULT_PAGING_OFFSET = 0;
    const MAX_PAGING_LIMIT = 100;

    const PREFIX_SORT_DESC = '-';

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    public function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    public function renderView($view, array $parameters = array())
    {
        //不推荐在API中使用renderView，不要继续使用
        @trigger_error("renderView in Api is not recommended, dont't use in the future，will removed soon", E_USER_DEPRECATED);

        return $this->container->get('templating')->render($view, $parameters);
    }

    /**
     * @return Biz
     */
    final public function getBiz()
    {
        return $this->biz;
    }

    final protected function service($service)
    {
        return $this->getBiz()->service($service);
    }

    /**
     * 检查每个API必需参数的完整性
     */
    protected function checkRequiredFields($requiredFields, $requestData)
    {
        $requestFields = array_keys($requestData);
        foreach ($requiredFields as $field) {
            if (!in_array($field, $requestFields)) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }
        }

        return $requestData;
    }

    protected function getOffsetAndLimit(ApiRequest $request)
    {
        $offset = $request->query->get('offset');
        $limit = $request->query->get('limit');

        if (!$offset) {
            $offset = static::DEFAULT_PAGING_OFFSET;
        }

        if (!$limit) {
            $limit = static::DEFAULT_PAGING_LIMIT;
        }

        return array($offset, $limit > self::MAX_PAGING_LIMIT ? self::MAX_PAGING_LIMIT : $limit);
    }

    protected function getSort(ApiRequest $request)
    {
        return $this->getSortByStr($request->query->get('sort'));
    }

    protected function getSortByStr($sortStr)
    {
        if ($sortStr) {
            $explodeSort = explode(',', $sortStr);

            $sort = array();
            foreach ($explodeSort as $part) {
                $prefix = substr($part, 0, 1);
                $field = str_replace(self::PREFIX_SORT_DESC, '', $part);
                if (self::PREFIX_SORT_DESC == $prefix) {
                    $sort[$field] = 'DESC';
                } else {
                    $sort[$field] = 'ASC';
                }
            }

            return $sort;
        }

        return array();
    }

    protected function dispatchEvent($eventName, Event $event)
    {
        $this->biz['dispatcher']->dispatch($eventName, $event);
    }

    public function supportMethods()
    {
        return array(
            static::METHOD_ADD,
            static::METHOD_GET,
            static::METHOD_SEARCH,
            static::METHOD_UPDATE,
            static::METHOD_REMOVE,
        );
    }

    /**
     * @return ObjectCombinationUtil
     */
    public function getOCUtil()
    {
        return $this->container->get('api.util.oc');
    }

    public function isPluginInstalled($code)
    {
        return $this->container->get('api.plugin.config.manager')->isPluginInstalled($code);
    }

    public function getClientIp()
    {
        return $this->container->get('request_stack')->getMasterRequest()->getClientIp();
    }

    public function invokeResource(ApiRequest $apiRequest)
    {
        return $this->container->get('api_resource_kernel')->handleApiRequest($apiRequest);
    }

    protected function makePagingObject($objects, $total, $offset, $limit)
    {
        return array(
            'data' => $objects,
            'paging' => array(
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
            ),
        );
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    /**
     * @return \Biz\S2B2C\Service\S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->getBiz()->service('S2B2C:S2B2CFacadeService');
    }
}
