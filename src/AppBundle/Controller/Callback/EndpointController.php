<?php

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EndpointController extends BaseController
{
    private $pool = array();

    public function publishAction(Request $request, $type)
    {
        //为了兼容老的云搜索
        if ($type == 'cloud_search') {
            $callbacks = $this->get('extension.manager')->getCallbacks();
            $biz = $this->getBiz();
            $processorInstance = $biz[$callbacks[$type]];

            return $processorInstance->execute($request);
        }

        $ac = $request->query->get('ac');
        if (strpos($ac, '.') === false) {
            throw new BadRequestHttpException('Invalid ac');
        }
        list($processor, $action) = explode('.', $ac);

        $instance = $this->getProcessInstance($type, $processor);

        if (!method_exists($instance, $action)) {
            throw new NotFoundHttpException(sprintf('Action %s not found', $action));
        }
        $data = $instance->$action($request);

        return new JsonResponse($data);
    }

    public function getProcessInstance($module, $processor)
    {
        $key = ucfirst($module) . '_' . ucfirst($processor);
        $processors = [
            'Marketing_Login' => \AppBundle\Controller\Callback\Marketing\Login::class,
            'Marketing_Orders' => \AppBundle\Controller\Callback\Marketing\Orders::class,
            'Marketing_Courses' => \AppBundle\Controller\Callback\Marketing\Courses::class,
            'ESLive_Callback' => \AppBundle\Controller\Callback\ESLive\Callback::class,
            'CloudFile_Files' => \AppBundle\Controller\Callback\CloudFile\Files::class,
        ];

        if (!isset($processors[$key])) {
            throw new NotFoundHttpException(sprintf('Processor %s not found', $key));
        }
        if (empty($this->pool[$key])) {
            $instance = new $processors[$key]();
            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($this->container);
            }

            $this->pool[$key] = $instance;
        }

        return $this->pool[$key];
    }
}
