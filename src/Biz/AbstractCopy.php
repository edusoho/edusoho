<?php

namespace Biz;

use Codeages\Biz\Framework\Context\Biz;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;

abstract class AbstractCopy
{
    private $logger;

    protected $copyChain;

    protected $biz;

    protected $auto;

    private $preCopyResult;

    private $doCopyResult;

    /**
     * Entity中待copy的字段列表
     *
     * @return array
     */
    abstract protected function getFields();

    abstract public function preCopy($source, $options);

    abstract public function doCopy($source, $options);

    public function __construct(Biz $biz, $copyChain, $auto = true)
    {
        $this->biz = $biz;
        $this->setCopyChain($copyChain);
        $this->auto = $auto;
    }

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/service.log', Logger::DEBUG));

        return $this->logger;
    }

    final public function copy($source, $options)
    {
        $this->preCopyResult = $this->preCopy($source, $options);
        $this->doCopyResult = $this->doCopy($source, $options);
        if ($this->auto) {
            $this->afterCopy($source, $options);
        }
    }

    public function afterCopy($source, $options)
    {
        $childrenNodes = $this->getChildrenNodes();

        if (is_array($this->doCopyResult)) {
            $options = array_merge($options, $this->doCopyResult);
        }
        foreach ($childrenNodes as $childrenNode) {
            $CopyClass = $childrenNode['class'];
            if (isset($childrenNode['auto'])) {
                $copyClass = new $CopyClass($this->biz, $childrenNode, $childrenNode['auto']);
            } else {
                $copyClass = new $CopyClass($this->biz, $childrenNode);
            }

            $copyClass->copy($source, $options);
        }
    }

    protected function getCurrentNodeName()
    {
        $className = explode('\\', get_class($this));
        $className = end($className);
        $className = str_replace('Copy', '', $className);

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1'.'-'.'$2', $className));
    }

    protected function partsFields($fields)
    {
        return ArrayToolkit::parts($fields, $this->getFields());
    }

    protected function getChildrenNodes()
    {
        return !empty($this->copyChain['children']) ? $this->copyChain['children'] : array();
    }

    protected function processChainsDoClone($chains, $source, $options)
    {
        foreach ($chains as  $currentNode) {
            $class = new $currentNode['class']($this->biz, $currentNode);
            $class->copy($source, $options);
        }
    }

    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function getCopyChain()
    {
        return $this->copyChain;
    }

    private function setCopyChain($copyChain)
    {
        $this->copyChain = $copyChain;
    }
}
