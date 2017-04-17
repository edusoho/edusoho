<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Util\ObjectCombinationUtil;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\Service\Common\ServiceKernel;

abstract class Resource
{
    private $logger;

    /**
     * @var Biz
     */
    private $biz;

    const METHOD_SEARCH = 'search';
    const METHOD_GET = 'get';
    const METHOD_ADD = 'add';
    const METHOD_REMOVE = 'remove';
    const METHOD_UPDATE = 'update';
    
    const DEFAULT_PAGING_LIMIT = 10;
    const DEFAULT_PAGING_OFFSET = 0;
    
    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $router = $this->biz['api.router'];
        return $router->generate($route, $parameters, $referenceType);
    }

    public function renderView($view, array $parameters = array())
    {
        $templating = $this->biz['api.templating'];
        return $templating->render($view, $parameters);
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
                throw new InvalidArgumentException("缺少必需的请求参数{$field}");
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

        return array($offset, $limit);
    }

    public function supportMethods()
    {
        return array(
            static::METHOD_ADD,
            static::METHOD_GET,
            static::METHOD_SEARCH,
            static::METHOD_UPDATE,
            static::METHOD_REMOVE
        );
    }

    /**
     * @return ObjectCombinationUtil
     */
    public function getOCUtil()
    {
        $biz = $this->getBiz();
        return $biz['api.util.oc'];
    }

    public function isPluginInstalled($code)
    {
        $biz = $this->getBiz();
        return $biz['api.plugin.config.manager']->isPluginInstalled($code);
    }
    
    protected function makePagingObject($objects, $total, $offset, $limit)
    {
        return array(
            'data' => $objects,
            'paging' => array(
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit
            )
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

    protected function addError($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->error($message);
    }

    protected function addDebug($logName, $message)
    {
        if (!$this->isDebug()) {
            return;
        }
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->debug($message);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function isDebug()
    {
        return 'dev' == $this->getServiceKernel()->getEnvironment();
    }

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($this->biz['kernel.logs_dir'].'/api.log', Logger::DEBUG));

        return $this->logger;
    }
}