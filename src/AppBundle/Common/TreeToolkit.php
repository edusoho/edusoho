<?php

namespace AppBundle\Common;

class TreeToolkit
{
    /**
     * @param array $flat
     * @param $sortKey
     * @param int    $parentId
     * @param string $parentKey
     *
     * @return array
     */
    public static function makeTree(array $flat, $parentId = 0, $parentKey = 'parentId')
    {
        $tree = self::makeParentTree($flat, $parentId, $parentKey);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::maketree($flat, $value['id'], $parentKey);
        }

        return $tree;
    }

    public static function makeSortTree(array $flat, $parentId = 0, $parentKey = 'parentId', $sortKey)
    {
        if (empty($sortKey)) {
            throw new \Exception('sort key can not be empty');
        }

        $tree = self::makeParentTree($flat, $parentId, $parentKey, $sortKey);

        foreach ($tree as $key => $value) {
            $tree[$key]['children'] = self::makeSortTree($flat, $value['id'], $parentKey, $sortKey);
        }

        return $tree;
    }

    private static function makeParentTree(array $flat, $parentId, $parentKey, $sortKey = '')
    {
        $filtered = array();

        if (empty($parentId)) {
            $parentId = self::generateParentId($flat, $parentKey);
        }

        foreach ($flat as $value) {
            if ($value[$parentKey] == $parentId) {
                $filtered[] = $value;
            }
        }

        if (!empty($sortKey)) {
            $sortArray = ArrayToolkit::column($filtered, $sortKey);
            array_multisort($sortArray, $filtered);
        }

        return $filtered;
    }

    private static function generateParentId($flat, $parentKey)
    {
        $parentIds = ArrayToolkit::column($flat, $parentKey);
        sort($parentIds);

        return array_shift($parentIds);
    }
}
