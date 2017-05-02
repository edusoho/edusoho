<?php

namespace Biz\Course\Copy2;

abstract class AbstractEntityCopy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    //fields to copy
    abstract protected function getFields();

    //copy logic
    abstract public function copy($source, $config = array());

    protected function copyFields($source)
    {
        $fields = $this->getFields();

        $new = array();
        foreach ($fields as $field) {
            if (!empty($source[$field]) || $source[$field] == 0) {
                $new[$field] = $source[$field];
            }
        }

        return $new;
    }

    //@todo refact
    protected function debug($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->debug($message);
    }

    protected function error($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->error($message);
    }

    private function getLogger($name)
    {
        $factory = $this->biz['logger'];

        return $factory($name, 'service');
    }
}
