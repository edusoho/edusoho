<?php

namespace AppBundle\Controller\Callback\Marketing;

use AppBundle\Controller\Callback\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Context\Biz;


class MarketingProcessor extends BaseController implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;

    }

    private $pool = array();

    public function getInstanceClassMap($type)
    {
        $namespace = __NAMESPACE__;

        $classMap = array(
            'courses.search' => $namespace.'\\CourseSetController',
            'courses.get' => $namespace.'\\CourseController',
            'orders.accept' => $namespace.'\\OrderController',
        );

        if (!isset($classMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Provider not available: %s', $type));
        }

        return $classMap[$type];
    }

    public function getProcessInstance($type)
    {
        if (empty($this->pool[$type])) {
            $class = $this->getInstanceClassMap($type);
            $instance = new $class($this->container);
            $this->pool[$type] = $instance;
        }

        return $this->pool[$type];
    }

    public function execute(Request $request)
    {
        $logger = $this->getBiz()['logger'];
        $logger->debug('营销平台callback');
        $ac = $request->query->get('ac');
        $logger->debug('callback,action:'.$ac);
        if (empty($ac))  {
            throw new \InvalidArgumentException('找不到合法的请求');
        }
        $instance = $this->getProcessInstance($ac);
        $logger->debug('callback,处理实例:'.get_class($instance));
        $data = $instance->indexAction($request);
        $json = new JsonResponse($data);
        return $json;
       
    }
}
