<?php

namespace Codeages\Plumber;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractWorker implements WorkerInterface, ContainerAwareInterface, ProcessAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use ProcessAwareTrait;
    use LoggerAwareTrait;
}
