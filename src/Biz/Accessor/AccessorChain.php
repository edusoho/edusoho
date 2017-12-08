<?php

namespace Biz\Accessor;

class AccessorChain
{
    /**
     * @var \ArrayIterator
     */
    private $accessors;

    public function __construct()
    {
        $this->accessors = new \ArrayIterator();
    }

    /**
     * @param $accessor
     * @param $priority int 优先级，数字越大越优先
     */
    public function add(AccessorInterface $accessor, $priority)
    {
        $this->accessors->append(array(
            'name' => substr(strrchr(get_class($accessor), '\\'), 1),
            'accessor' => $accessor,
            'priority' => $priority,
        ));

        $this->accessors->uasort(function ($a1, $a2) {
            return $a1['priority'] < $a2['priority'];
        });
    }

    /**
     * @param $name
     *
     * @return \Biz\Accessor\AccessorAdapter|null
     */
    public function getAccessor($name)
    {
        foreach ($this->accessors as $accessor) {
            if ($accessor['name'] == $name) {
                return $accessor['accessor'];
            }
        }

        return null;
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
        foreach ($this->accessors as $accessorArr) {
            /** @var \Biz\Accessor\AccessorAdapter $realAccessor */
            $realAccessor = $accessorArr['accessor'];
            $result = $realAccessor->process($bean);

            if (null !== $result) {
                return $result;
            }
        }

        return array('code' => AccessorInterface::SUCCESS);
    }
}
