<?php
namespace Topxia\Component\Payment;

abstract class Request {

    protected $params = array();

    protected $options = array();

    public function __construct(array $options = null)
    {
        $this->options = $options;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    abstract public function form();

    abstract public function signParams($params);
}