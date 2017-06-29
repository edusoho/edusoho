<?php

namespace Biz\File\FireWall;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class FireWallFactory
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $targetType
     *
     * @return FireWallInterface
     *
     * @throws InvalidArgumentException
     */
    public function create($targetType)
    {
        if (empty($targetType)) {
            throw new InvalidArgumentException('Resource  targetType  argument missing.');
        }

        $targetTypes = explode('.', $targetType);
        $class = __NAMESPACE__.'\\'.ucfirst($targetTypes[0]).'FileFireWall';

        return new $class($this->biz);
    }
}
