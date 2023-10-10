<?php

namespace AppBundle\Common;

class ChangelogToolkit
{
    const CHANGELOG_NEW = '新增';

    const CHANGELOG_FIX = '修复';

    const CHANGELOG_OPTIMIZATION = '优化';

    public static function parseSingleChangelog($changelogStr)
    {
        $result = [
            'items' => [],
        ];
        $pattern = "/^(?:\S|\s)*((?:[1-9]\d|[1-9])(?:\.(?:[1-9]\d|\d)){2})\s*(?:\(|（)(\S*)(?:\)|）)\s*/";
        preg_match($pattern, $changelogStr, $metas);
        if (!empty($metas)) {
            $result['version'] = trim($metas['1']);
            $result['date'] = trim($metas['2']);
        }

        $spiltPattern = '/'.PHP_EOL.'/';
        $changelogArr = preg_split($spiltPattern, $changelogStr, -1, PREG_SPLIT_NO_EMPTY);
        $result['items'] = self::matchItems($changelogArr);

        return $result;
    }

    protected static function matchItems($changelogArr)
    {
        $items[0] = explode(' ', $changelogArr[2])[0] ?? '';
        $items[1] = $changelogArr[3] ?? '';
        $items[1] .= $changelogArr[4] ?? '';
        $items[2] = $changelogArr[5] ?? '';

        return $items;
    }
}
