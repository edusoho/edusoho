<?php

namespace Biz\Sensitive\Service\Impl;

use Biz\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Sensitive\Service\SensitiveService;
use Biz\Sensitive\SensitiveException;

class SensitiveServiceImpl extends BaseService implements SensitiveService
{
    public function sensitiveCheck($text, $type = '')
    {
        $bannedResult = $this->bannedKeyword($text, $type);

        if ($bannedResult['success']) {
            $this->createNewException(SensitiveException::FORBIDDEN_WORDS());
        } else {
            return $this->replaceText($text, $type);
        }
    }

    protected function bannedKeyword($text, $type = '')
    {
        //预处理内容
        $text = strip_tags($text);
        $text = $this->semiangleTofullangle($text);
        $text = $this->plainTextFilter($text);

        $rows = $this->getSensitiveDao()->findByState('banned');

        if (empty($rows)) {
            return array('success' => false, 'text' => $text);
        }

        $keywords = array_column($rows, 'name');

        $chunkKeywords = array_chunk($keywords, 100);

        foreach ($chunkKeywords as $chunkKeyword) {
            $pattern = '/('.implode('|', $chunkKeyword).')/i';
            $matched = preg_match($pattern, $text, $match);
            if ($matched) {
                break;
            }
        }

        if (!$matched) {
            return array('success' => false, 'text' => $text);
        }

        $keyword = $this->flagReplaceReverse($match[1]);

        $bannedKeyword = $this->getSensitiveDao()->getByName($keyword);

        if (empty($bannedKeyword)) {
            return array('success' => false, 'text' => $text);
        }

        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser->id);
        $env = $this->getEnvVariable();
        $banlog = array(
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state' => $bannedKeyword['state'],
            'text' => $text,
            'userId' => $user ? $user['id'] : 0,
            'ip' => empty($user['loginIp']) ? 0 : $user['loginIp'],
            'createdTime' => time(),
        );

        $this->getBanlogDao()->create($banlog);

        $this->getSensitiveDao()->wave(array($bannedKeyword['id']), array('bannedNum' => 1));

        return array('success' => true, 'text' => $text);
    }

    protected function replaceText($text, $type = '')
    {
        $rows = $this->getSensitiveDao()->findByState('replaced');

        if (empty($rows)) {
            return $text;
        }

        $keywords = array_column($rows, 'name');

        $chunkKeywords = array_chunk($keywords, 100);

        $matchs = array();
        $matcheds = 0;
        $replacedText = $text;
        foreach ($chunkKeywords as $chunkKeyword) {
            $pattern = '/('.implode('|', $chunkKeyword).')/i';
            $matched = preg_match_all($pattern, $text, $match);
            if ($matched) {
                $matchs = array_merge($matchs, $match[0]);
                $replacedText = preg_replace($pattern, '*', $replacedText);
            }
            $matcheds += $matched;
        }

        if (!$matcheds) {
            return $text;
        }

        $keywords = array_unique($matchs);

        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser->id);
        $env = $this->getEnvVariable();
        foreach ($keywords as $key => $value) {
            $value = $this->flagReplaceReverse($value);
            $keyword = $this->getSensitiveDao()->getByName($value);
            $banlog = array(
                'keywordId' => $keyword['id'],
                'keywordName' => $keyword['name'],
                'state' => $keyword['state'],
                'text' => $text,
                'userId' => $user ? $user['id'] : 0,
                'ip' => empty($user['loginIp']) ? 0 : $user['loginIp'],
                'createdTime' => time(),
            );

            $this->getBanlogDao()->create($banlog);

            $this->getSensitiveDao()->wave(array($keyword['id']), array('bannedNum' => 1));
        }

        return $replacedText;
    }

    public function scanText($text)
    {
        //预处理内容
        $text = $this->semiangleTofullangle($text);
        $text = $this->plainTextFilter($text);

        $rows = $this->getSensitiveDao()->findAllKeywords();

        if (empty($rows)) {
            return false;
        }

        $keywords = array_column($rows, 'name');

        $chunkKeywords = array_chunk($keywords, 100);

        foreach ($chunkKeywords as $chunkKeyword) {
            $pattern = '/('.implode('|', $chunkKeyword).')/i';
            $matched = preg_match($pattern, $text, $match);
            if ($matched) {
                break;
            }
        }

        if (!$matched) {
            return false;
        }

        $keyword = $this->flagReplaceReverse($match[1]);

        $bannedKeyword = $this->getSensitiveDao()->getByName($keyword);

        if (empty($bannedKeyword)) {
            return false;
        }

        $currentUser = $this->getCurrentUser();

        $env = $this->getEnvVariable();
        $banlog = array(
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state' => $bannedKeyword['state'],
            'text' => $text,
            'userId' => $currentUser ? $currentUser['id'] : 0,
            'ip' => 0,
            'createdTime' => time(),
        );

        $this->getBanlogDao()->create($banlog);

        $this->getSensitiveDao()->wave(array($bannedKeyword['id']), array('bannedNum' => 1));

        return $match[1];
    }

    public function getKeywordByName($name)
    {
        $name = $this->flagReplaceReverse($name);

        return $this->getSensitiveDao()->getByName($name);
    }

    public function findAllKeywords()
    {
        return $this->getSensitiveDao()->findAllKeywords();
    }

    public function addKeyword($keyword, $state)
    {
        $keyword = $this->flagReplaceReverse($keyword);

        $conditions = array(
            'name' => $keyword,
            'state' => $state,
            'createdTime' => time(),
        );
        $result = $this->getSensitiveDao()->create($conditions);

        return $result;
    }

    public function deleteKeyword($id)
    {
        $keyword = $this->getSensitiveDao()->get($id);
        $result = $this->getSensitiveDao()->delete($id);

        return $result;
    }

    public function updateKeyword($id, $fields)
    {
        $result = $this->getSensitiveDao()->update($id, $fields);

        return $result;
    }

    public function searchkeywordsCount($conditions)
    {
        return $this->getSensitiveDao()->count($conditions);
    }

    public function searchKeywords($conditions, $orderBy, $start, $limit)
    {
        return $this->getSensitiveDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchBanlogsCount($conditions)
    {
        return $this->getBanlogDao()->count($conditions);
    }

    public function searchBanlogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getBanlogDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchBanlogsByUserIds($userIds, $orderBy, $start, $limit)
    {
        return $this->getBanlogDao()->searchBanlogsByUserIds($userIds, $orderBy, $start, $limit);
    }

    /**
     * 移除不可见字符.
     *
     * @param
     */
    private function plainTextFilter($text, $strictmodel = false)
    {
        $text = trim(str_replace('&nbsp;', ' ', $text));
        if ($strictmodel) {
            //严格模式，仅保留中文
            $text = preg_replace('/[^\x{4e00}-\x{9fa5}]/iu', '', $text);
        }
        //非严格模式，保留中文汉子，数字
        return preg_replace('/[^a-zA-Z0-9\x{4e00}-\x{9fa5}]/iu', '', $text);
    }

    /**
     * @param $text 要转换的字符串
     * @param $flag 全角半角转换      true:半角， false:全角, 默认转换为半角
     */
    private function semiangleTofullangle($text, $flag = true)
    {
        $fullangle = array(
            '０', '１', '２', '３', '４',
            '５', '６', '７', '８', '９',
            'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ',
            'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ',
            'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ',
            'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ',
            'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ',
            'Ｚ', 'ａ', 'ｂ', 'ｃ', 'ｄ',
            'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ',
            'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ',
            'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ',
            'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ',
            'ｙ', 'ｚ', '－', '　', '：',
            '．', '，', '／', '％', '＃',
            '！', '＠', '＆', '（', '）',
            '＜', '＞', '＂', '＇', '？',
            '［', '］', '｛', '｝', '＼',
            '｜', '＋', '＝', '＿', '＾',
            '￥', '￣', '｀',
        );
        $semiangle = array( // 半角
            '0', '1', '2', '3', '4',
            '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y',
            'Z', 'a', 'b', 'c', 'd',
            'e', 'f', 'g', 'h', 'i',
            'j', 'k', 'l', 'm', 'n',
            'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x',
            'y', 'z', '-', ' ', ':',
            '.', ',', '/', '%', '#',
            '!', '@', '&', '(', ')',
            '<', '>', '"', '\'', '?',
            '[', ']', '{', '}', '\\',
            '|', '+', '=', '_', '^',
            '$', '~', '`',
        );
        //true 全角->半角
        return $flag ? str_replace($fullangle, $semiangle, $text) : str_replace($semiangle, $fullangle, $text);
    }

    private function flagReplaceReverse($content)
    {
        $contentFilter = preg_quote($content, '/');

        return $contentFilter;
    }

    private function getEnvVariable()
    {
        return ServiceKernel::instance()->getEnvVariable();
    }

    protected function getSensitiveDao()
    {
        return $this->createDao('Sensitive:SensitiveDao');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getBanlogDao()
    {
        return $this->createDao('Sensitive:KeywordBanlogDao');
    }
}
