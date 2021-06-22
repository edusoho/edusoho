<?php

namespace Biz\WrongBook\Pool;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractPool
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getPoolTarget($report);

    public function prepareSceneIds($poolClass, $poolId, $conditions)
    {
        $sceneIds = [];
        foreach ($conditions as $condition => $methodValue) {
            $method = 'findSceneIdsBy'.ucfirst($condition);
            if (method_exists($poolClass, $method)) {
                $findSceneIds = $poolClass->$method($poolId, $methodValue);
                $sceneIds = empty($sceneIds) ? $findSceneIds : array_intersect($sceneIds, $findSceneIds);
            }
        }

        return $sceneIds;
    }
}
