<?php

namespace Biz\Activity\Listener;

use Codeages\Biz\Framework\Context\Biz;

abstract class Listener
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function handle($activity, $data);

    /**
     * @return Biz
     */
    final protected function getBiz()
    {
        return $this->biz;
    }
}
