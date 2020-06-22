<?php

namespace Biz\Oauth;

use AppBundle\Common\Exception\UnexpectedValueException;
use Codeages\Biz\Framework\Context\Biz;
use Firebase\JWT\JWT;

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