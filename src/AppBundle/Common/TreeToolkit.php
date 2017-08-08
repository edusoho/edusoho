<?php

namespace AppBundle\Common;

use AppBundle\Common\Tree;

class TreeToolkit
{
    /**
     * [maketree description].
     *
     * @param array    $data     需要排序的数组,本身要支持层级关系
     * @param [string] $parentId
     * @param string   $sort     排序的字段
     *
     * @return [array] tree data
     */
    public static function makeTree(array $data, $sort, $parentId = 0)
    {
        $tree = self::makeParentTree($data, $sort, $parentId);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::maketree($data, $sort, $value['id']);
        }

        return $tree;
    }

    /**
     * [getTreeIds description]
     *
     * @param array $data     支持层级关系的数组
     * @param int   $parentId 层级父ID
     * @param array $treeIds  该层级下的所有id
     *
     * @return [array] tree ids
     */
    public static function getTreeIds(array $data, $parentId = 0)
    {
        $dataTree = Tree::buildWithArray($data, $parentId);

        return $dataTree->column('id');
    }

    private static function makeParentTree(array $data, $sort, $parentId)
    {
        $filtered = array();

        if (empty($parentId)) {
            $parentIds = self::generateParentId($data);
        }

        foreach ($data as $value) {
            if ($value['parentId'] == $parentId) {
                $filtered[] = $value;
            }
        }

        $sortArray = ArrayToolkit::column($filtered, $sort);

        array_multisort($sortArray, $filtered);

        return $filtered;
    }

    private static function generateParentId($data)
    {
        $parentIds = ArrayToolkit::column($data, 'parentId');
        sort($parentIds);

        return array_shift($parentIds);
    }
}
