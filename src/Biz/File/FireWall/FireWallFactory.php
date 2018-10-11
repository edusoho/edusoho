<?php

namespace Biz\File\FireWall;

use Biz\Common\CommonException;
use Codeages\Biz\Framework\Context\Biz;

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
     * @throws CommonException
     */
    public function create($targetType)
    {
        if (empty($targetType)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $targetTypes = explode('.', $targetType);
        $class = __NAMESPACE__.'\\'.ucfirst($targetTypes[0]).'FileFireWall';

        return new $class($this->biz);
    }
}
