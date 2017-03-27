<?php

namespace Codeages\Biz\Framework\Context;

interface BootableProviderInterface
{
    public function boot(Biz $biz);
}
