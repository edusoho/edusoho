<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EndpointController extends BaseController
{
    private $pool = array();

    public function publishAction(Request $request, $type)
    {
        //为了兼容老的云搜索
        if ($type == 'cloud_search') {
            $callbacks = $this->get('extension.manager')->getCallbacks();
            $biz = $this->getBiz();
            $processerInstance = $biz[$callbacks[$type]];

            return $processerInstance->execute($request);
        } else {
            $ac = $request->query->get('ac');
            if (strpos($ac, '.') === false) {
                throw new \InvalidArgumentException('找不到合法的请求');
            }
            list($processer, $action) = explode('.', $ac);
            $instance = $this->getProcessInstance($type, $processer);

            $data = $instance->$action($request);

            return  new JsonResponse($data);
        }
    }

    private function getProcessInstanceClass($module, $processer)
    {
        $module = ucfirst($module);
        $className = ucfirst($processer);

        $class = __NAMESPACE__."\\{$module}\\{$className}";
        if (!class_exists($class)) {
            throw new \Exception("{$module}-{$className} is not exist!");
        }

        return $class;
    }

    public function getProcessInstance($module, $processer)
    {
        $key = $module.'_'.$processer;
        if (empty($this->pool[$key])) {
            $class = $this->getProcessInstanceClass($module, $processer);
            $instance = new $class($this);

            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($this->container);
            }

            $this->pool[$key] = $instance;
        }

        return $this->pool[$key];
    }
}
