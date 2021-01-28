<?php

namespace Biz\File\FireWall;

use Codeages\Biz\Framework\Context\Biz;

class BaseFireWall
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function getCurrentUser()
    {
        return $this->biz['user'];
    }
}
