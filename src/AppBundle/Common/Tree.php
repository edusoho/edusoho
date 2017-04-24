<?php

namespace AppBundle\Common;

/**
 * Class Tree 多叉树 数据结构类
 * 对树的操作是递归的，如有特殊需求导致递归会爆栈 可以改用stack来实现递归的效果.
 */
class Tree
{
    /**
     * @var array<Tree>
     */
    private $children = array();

    /**
     * @var Tree
     */
    private $parent;

    /**
     * @var mixed
     */
    public $data;

    public function __construct($data = array(), Tree $parent = null)
    {
        $this->data = $data;
        $this->parent = $parent;
    }

    /**
     * Like ArrayToolkit::column.
     *
     * @param $key
     *
     * @return mixed
     */
    public function column($key)
    {
        return $this->reduce(function ($ret, $tree) use ($key) {
            if (!empty($tree->data[$key])) {
                array_push($ret, $tree->data[$key]);
            }

            return $ret;
        }, array());
    }

    /**
     * each Tree Node call closure.
     *
     * @param \Closure $closure
     *
     * @return $this
     */
    public function each(\Closure $closure)
    {
        $closure($this);
        foreach ($this->getChildren() as $child) {
            $child->each($closure);
        }

        return $this;
    }

    /**
     * Like array_reduce.
     *
     * @see http://php.net/manual/zh/function.array-reduce.php
     *
     * @param \Closure $closure
     * @param null     $initial
     *
     * @return mixed
     */
    public function reduce(\Closure $closure, $initial = null)
    {
        is_null($initial) ? $ret = $this : $ret = $initial;

        $ret = $closure($ret, $this);

        foreach ($this->children as $child) {
            $ret = $child->reduce($closure, $ret);
        }

        return $ret;
    }

    /**
     * @return array<type($this->data)>
     */
    public function toArray()
    {
        $ret = $this->data;
        $ret['children'] = array();

        foreach ($this->getChildren() as $child) {
            array_push($ret['children'], $child->toArray());
        }

        return $ret;
    }

    /**
     * @param \Closure $closure
     *
     * @return Tree
     */
    public function find(\Closure $closure)
    {
        if ($closure($this)) {
            return $this;
        }
        $ret = null;
        foreach ($this->children as $child) {
            $ret = $child->find($closure);
            if (!is_null($ret)) {
                break;
            }
        }

        return $ret;
    }

    public function findToParent(\Closure $closure)
    {
        $parent = $this;
        $ret = array();
        while (!is_null($parent)) {
            if ($closure($parent)) {
                $ret[] = $parent;
            }

            $parent = $parent->getParent();
        }

        return array_pop($ret);
    }

    public static function buildWithArray(array $input, $rootId = 0, $key = 'id', $parentKey = 'parentId')
    {
        $root = new self(array(
            $key => $rootId,
        ));

        // 方便找到父节点
        $map = array(
            $rootId => $root,
        );

        $buildingArray = $input;

        while (!empty($buildingArray)) {
            $buildingCount = count($buildingArray);
            foreach ($buildingArray as $index => $value) {
                if (isset($map[$value[$parentKey]])) {
                    $parent = $map[$value[$parentKey]];
                    $tree = new self($value, $parent);
                    $parent->addChild($tree);
                    $map[$value[$key]] = $tree;
                    unset($buildingArray[$index]);
                }
            }

            //一次构建树后剩下元素不变。 说明这些元素的父节点不存在树的节点里，是构建不出的树的
            if ($buildingCount === count($buildingArray)) {
                break;
            }
        }

        return $root;
    }

    /**
     * @param Tree $child
     *
     * @return $this
     */
    public function addChild(Tree $child)
    {
        array_push($this->children, $child);

        return $this;
    }

    /**
     * @return array<Tree>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Tree
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Tree $parent
     *
     * @return $this
     */
    public function setParent(Tree $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }
}
