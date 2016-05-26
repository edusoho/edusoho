<?php
namespace Topxia\Common;

class TreeToolkit
{
    /**
     * [maketree description]
     * @param  array    $data       需要排序的数组,本身要支持层级关系
     * @param  [string] $parentId
     * @param  string   $sort       排序的字段
     * @return [array]  tree data
     */

    public static function maketree(array $data, $parentId, $sort)
    {
        $tree = self::makeRootTree($data, $parentId);
        $tree = self::sortData($tree, $sort);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::maketree($data, $value['id'], $sort);
        }

        return array_values($tree);
    }

    private static function sortData($tree, $sort)
    {
        if (empty($sort)) {
            return $tree;
        }

        $tree = ArrayToolkit::index($tree, $sort);
        ksort($tree, SORT_NUMERIC);
        return $tree;
    }

    private static function makeRootTree(array $categories, $parentId)
    {
        $filtered = array();

        foreach ($categories as $value) {
            if ($value['parentId'] == $parentId) {
                $filtered[] = $value;
            }
        }

        return $filtered;
    }
}
