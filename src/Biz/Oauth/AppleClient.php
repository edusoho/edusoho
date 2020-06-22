<?php

namespace Biz\Oauth;

use Codeages\Biz\Framework\Context\Biz;

class AppleClient
{
    protected $biz;

    protected $config;

    public function __construct(Biz $biz, $config)
    {
        $this->biz = $biz;

        $this->config = $config;
    }
}
