<?php

namespace AppBundle\DataCollector;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

class CloudApiDataCollector extends DataCollector implements LateDataCollectorInterface
{
    protected $biz;

    protected $cloudApiCollector;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function getName()
    {
        return 'app.cloud_api_collector';
    }

    public function lateCollect()
    {
        $this->data['logs'] = $this->biz->offsetExists('cloud_api_collector') ? $this->biz->offsetGet('cloud_api_collector') : array();
        $this->data['count'] = count($this->data['logs']);
    }

    public function getCount()
    {
        return $this->data['count'];
    }

    public function getLogs()
    {
        return $this->data['logs'];
    }
}
