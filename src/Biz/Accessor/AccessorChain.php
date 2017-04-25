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

    //todo doc
    public function process($bean)
    {
        $accessors = $this->accessors;
        if (empty($accessors)) {
            return array('code' => 'success');
        }

        foreach ($accessors as $accessor) {
            $result = $accessor['accessor']->access($bean);

            if ($result !== null) {
                return $result;
            }
        }

        return array('code' => 'success');
    }
}
