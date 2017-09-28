<?php

namespace AppBundle\Common;

class TreeToolkit
{
    /**
     * @param array $data 需要排序的数组,本身要支持层级关系
     * @param $sort
     * @param int    $parentId
     * @param string $parentKey
     *
     * @return array
     */
    public static function makeTree(array $data, $sort, $parentId = 0, $parentKey = 'parentId')
    {
        $tree = self::makeParentTree($data, $sort, $parentId, $parentKey);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::makeTree($data, $sort, $value['id'], $parentKey);
        }

        return $tree;
    }

    private static function makeParentTree(array $data, $sort, $parentId, $parentKey)
    {
        $filtered = array();

        if (empty($parentId)) {
            $parentIds = self::generateParentId($data, $parentKey);
        }

        foreach ($data as $value) {
            if ($value[$parentKey] == $parentId) {
                $filtered[] = $value;
            }
        }

        $sortArray = ArrayToolkit::column($filtered, $sort);

        array_multisort($sortArray, $filtered);

        return $filtered;
    }

    private static function generateParentId($data, $parentKey)
    {
        $parentIds = ArrayToolkit::column($data, $parentKey);
        sort($parentIds);

        return array_shift($parentIds);
    }
}
