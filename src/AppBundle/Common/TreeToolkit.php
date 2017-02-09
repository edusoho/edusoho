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

    public static function makeTree(array $data, $sort, $parentId = 0)
    {
        $tree = self::makeParentTree($data, $sort, $parentId);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::maketree($data, $sort, $value['id']);
        }

        return $tree;
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
