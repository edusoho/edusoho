<?php

namespace Biz\Accessor;

class AccessorChain
{
    /**
     * @var array
     */
    private $accessors;

    /**
     * @param $accessor
     * @param $priority int 优先级，数字越大越优先
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

    /**
     * @param $bean  object 待校验的对象，比如classroom、course等
     *
     * @return array result with code and msg
     *               code = success 校验通过
     *               code = other value 校验不通过，code为错误码，msg为错误信息
     */
    public function process($bean)
    {
        $accessors = $this->accessors;
        if (empty($accessors)) {
            return array('code' => AccessorInterface::SUCCESS);
        }

        foreach ($accessors as $accessor) {
            $result = $accessor['accessor']->access($bean);

            if ($result !== null) {
                return $result;
            }
        }

        return array('code' => AccessorInterface::SUCCESS);
    }
}
