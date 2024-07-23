<?php

namespace Biz\Sensitive\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Sensitive\SensitiveException;
use Biz\Sensitive\Service\SensitiveService;
use Topxia\Service\Common\ServiceKernel;

class SensitiveServiceImpl extends BaseService implements SensitiveService
{
    public function sensitiveCheck($text, $type = '')
    {
        $bannedResult = $this->bannedKeyword($text, $type);

        if ($bannedResult['success']) {
            $this->createNewException(SensitiveException::FORBIDDEN_WORDS());
        } else {
            $handled = $this->handleContent($text, $type);

            return $handled['content'];
        }
    }

    public function sensitiveCheckResult($content, $targetType = '', $targetId = '')
    {
        $bannedResult = $this->bannedKeyword($content, $targetType);
        if ($bannedResult['success']) {
            $this->createNewException(SensitiveException::FORBIDDEN_WORDS());
        } else {
            return $this->handleContent($content, $targetType, $targetId);
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
            return ['success' => false, 'text' => $text];
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
            return ['success' => false, 'text' => $text];
        }

        $keyword = $this->flagReplaceReverse($match[1]);

        $bannedKeyword = $this->getSensitiveDao()->getByName($keyword);

        if (empty($bannedKeyword)) {
            return ['success' => false, 'text' => $text];
        }

        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser->id);
        $banlog = [
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state' => $bannedKeyword['state'],
            'text' => $text,
            'userId' => $user ? $user['id'] : 0,
            'ip' => empty($user['loginIp']) ? 0 : $user['loginIp'],
            'createdTime' => time(),
        ];

        $this->getBanlogDao()->create($banlog);

        $this->getSensitiveDao()->wave([$bannedKeyword['id']], ['bannedNum' => 1]);

        return ['success' => true, 'text' => $text];
    }

    protected function handleContent($text, $type = '', $targetId = '')
    {
        $rows = $this->getSensitiveDao()->findByState('replaced');

        if (empty($rows)) {
            return ['content' => $text, 'originContent' => $text, 'keywords' => []];
        }

        $keywords = array_column($rows, 'name');

        $chunkKeywords = array_chunk($keywords, 100);

        $matchs = [];
        $matcheds = 0;
        $untaggedText = strip_tags($text);
        foreach ($chunkKeywords as $chunkKeyword) {
            $pattern = '/('.implode('|', $chunkKeyword).')/i';
            $matched = preg_match_all($pattern, $untaggedText, $match);
            if ($matched) {
                $matchs = array_merge($matchs, $match[0]);
            }
            $matcheds += $matched;
        }
        $replacedText = preg_replace('/('.implode('|', $matchs).')/i', '*', $text);

        if (!$matcheds) {
            return ['content' => $text, 'originContent' => $text, 'keywords' => []];
        }

        $hits = array_unique($matchs);

        $currentUser = $this->getCurrentUser();
        $user = $this->getUserService()->getUser($currentUser->id);
        foreach ($hits as $key => $value) {
            $value = $this->flagReplaceReverse($value);
            $keyword = $this->getSensitiveDao()->getByName($value);
            $banlog = [
                'keywordId' => $keyword['id'],
                'keywordName' => $keyword['name'],
                'state' => $keyword['state'],
                'text' => $text,
                'userId' => $user ? $user['id'] : 0,
                'ip' => empty($user['loginIp']) ? 0 : $user['loginIp'],
                'createdTime' => time(),
            ];

            $this->getBanlogDao()->create($banlog);

            $this->getSensitiveDao()->wave([$keyword['id']], ['bannedNum' => 1]);
        }

        return ['content' => $replacedText, 'originContent' => $text, 'keywords' => $hits];
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
        $banlog = [
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state' => $bannedKeyword['state'],
            'text' => $text,
            'userId' => $currentUser ? $currentUser['id'] : 0,
            'ip' => 0,
            'createdTime' => time(),
        ];

        $this->getBanlogDao()->create($banlog);

        $this->getSensitiveDao()->wave([$bannedKeyword['id']], ['bannedNum' => 1]);

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

        $conditions = [
            'name' => $keyword,
            'state' => $state,
            'createdTime' => time(),
        ];
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
        $fields = ArrayToolkit::parts($fields, ['state', 'bannedNum']);

        $result = $this->getSensitiveDao()->update($id, $fields);

        return $result;
    }

    public function searchkeywordsCount($conditions)
    {
        return $this->getSensitiveDao()->count($this->prepareConditions($conditions));
    }

    public function searchKeywords($conditions, $orderBy, $start, $limit, array $columns = [])
    {
        return $this->getSensitiveDao()->search($this->prepareConditions($conditions), $orderBy, $start, $limit);
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

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['keyword'])) {
            if ('name' === $conditions['searchKeyWord']) {
                $conditions['keyword'] = $this->flagReplaceReverse($conditions['keyword']);
            }
        }

        return $conditions;
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
        $fullangle = [
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
        ];
        $semiangle = [ // 半角
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
        ];
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
