<?php

namespace AppBundle\Common;

class ChangelogToolkit
{
    const CHANGELOG_NEW = '新增';

    const CHANGELOG_FIX = '修复';

    const CHANGELOG_OPTIMIZATION = '优化';

    public static function parseSingleChangelog($changelogStr)
    {
        $pattern = '/^[^\d]+(\d+\.\d+\.\d+)\s*(\(|（)(\d+-\d+-\d+)(\)|）)([\s\S]*?)(【新增|【修复|【优化|新增|修复|优化)/';
        preg_match($pattern, $changelogStr, $metas);
        if (!empty($metas)) {
            $result['version'] = trim($metas[1]);
            $result['date'] = trim($metas[3]);
            $result['tips'] = trim($metas[5]);
        }
        $spiltPattern = '/'.PHP_EOL.'/';
        $changelogArr = preg_split($spiltPattern, $changelogStr, -1, PREG_SPLIT_NO_EMPTY);
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
