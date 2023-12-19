<?php

namespace AppBundle\Common;

class ChangelogToolkit
{
    const CHANGELOG_NEW = '新增';

    const CHANGELOG_FIX = '修复';

    const CHANGELOG_OPTIMIZATION = '优化';

    public static function parseSingleChangelog($changelogStr)
    {
        $spiltPattern = '/'.PHP_EOL.'/';
        $changelogArr = preg_split($spiltPattern, $changelogStr, -1, PREG_SPLIT_NO_EMPTY);

        //注意 changelog 写法一定要是标准写法否则匹配会失败
        $metas = explode(' ', $changelogArr[2] ?? '');
        $result = [];
        $result['version'] = $metas[0] ?? '-';
        $result['date'] = $metas[1] ? preg_replace('/^（|）|\(|\)$/', '', $metas[1]) : '-';
        $result['tips'] = $changelogArr[3] ?? '';
        $result['tips'] .= $changelogArr[4] ?? '';
        $result['items'] = self::matchItems($changelogArr);

        return $result;
    }

    protected static function matchItems($changelogArr)
    {
        $items = [];
        $types = [
            self::CHANGELOG_FIX,
            self::CHANGELOG_NEW,
            self::CHANGELOG_OPTIMIZATION,
        ];

        foreach ($changelogArr as $line) {
            foreach ($types as $type) {
                if (false !== strpos(trim($line), $type) && 0 == count($items)) {
                    $items[] = trim($line);
                }
            }
        }

        return $items;
    }
}
