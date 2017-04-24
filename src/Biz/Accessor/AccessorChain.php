<?php

namespace Biz\Accessor;

class AccessorChain
{
    private $accessors;

    /**
     * @param $accessor
     * @param $priority int 优先级，数字越小越优先
     */
    public function add(AccessorInterface $accessor, $priority)
    {
        $this->accessors[] = array(
            'accessor' => $accessor,
            'priority' => $priority,
        );

        uasort($this->accessors, function ($a1, $a2) {
            return $a1['priority'] < $a2['priority'];
        });
    }

    public function process($bean)
    {
        $accessors = $this->accessors;
        if (empty($accessors)) {
            return true;
        }

        foreach ($accessors as $accessor) {
            $result = $accessor->access($bean);

            if ($result !== true) {
                return $result;
            }
        }

        return true;
    }
}
