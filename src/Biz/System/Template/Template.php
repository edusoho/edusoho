<?php

namespace Biz\System\Template;

use Codeages\Biz\Framework\Context\Biz;

abstract class Template
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTemplate();

    protected function getHost()
    {
        $schema = (!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) ? 'https' : 'http';

        return $schema.'://'.$_SERVER['HTTP_HOST'];
    }
}
