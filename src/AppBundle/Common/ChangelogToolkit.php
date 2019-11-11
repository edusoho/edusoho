<?php

namespace AppBundle\Common;

class ChangelogToolkit
{
    const CHANGELOG_NEW = '新增';

    const CHANGELOG_FIX = '修复';

    const CHANGELOG_OPTIMIZATION = '优化';

    public static function parseSingleChangelog($changelogStr)
    {
        $result = array(
            'items' => array(),
        );
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
        $items = array();
        $types = array(
            self::CHANGELOG_FIX,
            self::CHANGELOG_NEW,
            self::CHANGELOG_OPTIMIZATION,
        );

        foreach ($types as $type) {
            foreach ($changelogArr as $line) {
                if (0 === strpos(trim($line), $type)) {
                    $items[] = trim($line);
                }
            }
        }

        return $items;
    }
}
