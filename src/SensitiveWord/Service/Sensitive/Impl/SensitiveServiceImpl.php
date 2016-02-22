<?php
namespace SensitiveWord\Service\Sensitive\Impl;

use Topxia\Service\Common\BaseService;
use SensitiveWord\Service\Sensitive\SensitiveService;

class SensitiveServiceImpl extends BaseService implements SensitiveService
{
    public function sensitiveCheck($text, $type = '')
    {
        $bannedResult = $this->bannedKeyword($text, $type);

        if ($bannedResult['success']) {
            throw $this->createServiceException('您填写的内容中包含敏感词!');
        } else {
            return $this->replaceText($text, $type);
        }
    }

    protected function bannedKeyword($text, $type = '')
    {
        //预处理内容
        $text = strip_tags($text);
        $text = $this->semiangleTofullangle($text);
        $text = $this->plainTextFilter($text, true);

        $rows = $this->getSensitiveDao()->findKeywordsByState('banned');

        if (empty($rows)) {
            return array('success' => false, 'text' => $text);
        }

        $keywords = array();

        foreach ($rows as $row) {
            $keywords[] = $row['name'];
        }

        $pattern = '/('.implode('|', $keywords).')/';
        $matched = preg_match($pattern, $text, $match);

        if (!$matched) {
            return array('success' => false, 'text' => $text);
        }

        $bannedKeyword = $this->getSensitiveDao()->getKeywordByName($match[1]);

        if (empty($bannedKeyword)) {
            return array('success' => false, 'text' => $text);
        }

        $currentUser = $this->getCurrentUser();
        $user        = $this->getUserService()->getUser($currentUser->id);
        $env         = $this->getEnvVariable();
        $banlog      = array(
            'keywordId'   => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state'       => $bannedKeyword['state'],
            'text'        => $text,
            'userId'      => $user ? $user['id'] : 0,
            'ip'          => empty($user['loginIp']) ? 0 : $user['loginIp'],
            'createdTime' => time()
        );

        $this->getBanlogDao()->addBanlog($banlog);

        $this->getSensitiveDao()->waveBannedNum($bannedKeyword['id'], 1);

        return array('success' => true, 'text' => $text);
    }

    protected function replaceText($text, $type = '')
    {
        $rows = $this->getSensitiveDao()->findKeywordsByState('replaced');

        if (empty($rows)) {
            return $text;
        }

        $keywords = array();

        foreach ($rows as $row) {
            $keywords[] = $row['name'];
        }

        $pattern = '/('.implode('|', $keywords).')/';
        $matched = preg_match_all($pattern, $text, $match);

        if (!$matched) {
            return $text;
        }

        $keywords = array_unique($match[0]);

        foreach ($keywords as $key => $value) {
            $keyword = $this->getSensitiveDao()->getKeywordByName($value);

            $currentUser = $this->getCurrentUser();
            $user        = $this->getUserService()->getUser($currentUser->id);
            $env         = $this->getEnvVariable();
            $banlog      = array(
                'keywordId'   => $keyword['id'],
                'keywordName' => $keyword['name'],
                'state'       => $keyword['state'],
                'text'        => $text,
                'userId'      => $user ? $user['id'] : 0,
                'ip'          => empty($user['loginIp']) ? 0 : $user['loginIp'],
                'createdTime' => time()
            );

            $this->getBanlogDao()->addBanlog($banlog);

            $this->getSensitiveDao()->waveBannedNum($keyword['id'], 1);
        }

        return preg_replace($pattern, '*', $text);
    }

    public function scanText($text)
    {
        //预处理内容
        $text = $this->semiangleTofullangle($text);
        $text = $this->plainTextFilter($text, true);

        $rows = $this->getSensitiveDao()->findAllKeywords();

        if (empty($rows)) {
            return false;
        }

        $keywords = array();

        foreach ($rows as $row) {
            $keywords[] = $row['name'];
        }

        $pattern = '/('.implode('|', $keywords).')/';
        $matched = preg_match($pattern, $text, $match);

        if (!$matched) {
            return false;
        }

        $bannedKeyword = $this->getSensitiveDao()->getKeywordByName($match[1]);

        if (empty($bannedKeyword)) {
            return false;
        }

        $currentUser = $this->getCurrentUser();

        $env    = $this->getEnvVariable();
        $banlog = array(
            'keywordId'   => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'state'       => $bannedKeyword['state'],
            'text'        => $text,
            'userId'      => $currentUser ? $currentUser['id'] : 0,
            'ip'          => 0,
            'createdTime' => time()
        );

        $this->getBanlogDao()->addBanlog($banlog);

        $this->getSensitiveDao()->waveBannedNum($bannedKeyword['id'], 1);

        return $match[1];
    }

    public function getKeywordByName($name)
    {
        return $this->getSensitiveDao()->getKeywordByName($name);
    }

    public function findAllKeywords()
    {
        return $this->getSensitiveDao()->findAllKeywords();
    }

    public function addKeyword($keyword, $state)
    {
        $conditions = array(
            'name'        => $keyword,
            'state'       => $state,
            'createdTime' => time()
        );
        $result = $this->getSensitiveDao()->addKeyword($conditions);

        $this->getKeywordFilter()->add($result);

        return $result;
    }

    public function deleteKeyword($id)
    {
        $keyword = $this->getSensitiveDao()->getKeyword($id);
        $result  = $this->getSensitiveDao()->deleteKeyword($id);
        $this->getKeywordFilter()->remove($keyword['name']);
        return $result;
    }

    public function updateKeyword($id, $conditions)
    {
        $result = $this->getSensitiveDao()->updateKeyword($id, $conditions);

        $this->getKeywordFilter()->update($result);

        return $result;
    }

    public function searchkeywordsCount($conditions)
    {
        return $this->getSensitiveDao()->searchkeywordsCount($conditions);
    }

    public function searchKeywords($conditions, $orderBy, $start, $limit)
    {
        return $this->getSensitiveDao()->searchKeywords($conditions, $orderBy, $start, $limit);
    }

    public function searchBanlogsCount($conditions)
    {
        return $this->getBanlogDao()->searchBanlogsCount($conditions);
    }

    public function searchBanlogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getBanlogDao()->searchBanlogs($conditions, $orderBy, $start, $limit);
    }

    public function searchBanlogsByUserIds($userIds, $orderBy, $start, $limit)
    {
        return $this->getBanlogDao()->searchBanlogsByUserIds($userIds, $orderBy, $start, $limit);
    }

    /**
     * 移除不可见字符
     *
     * @param
     */
    private function plainTextFilter($text, $strictmodel = false)
    {
        if ($strictmodel) {
            $text = preg_replace('/[^\x{4e00}-\x{9fa5}]/iu', '', $text);
            //严格模式，仅保留中文
        } else {
            $text = preg_replace('/[^a-zA-Z0-9\x{4e00}-\x{9fa5}]/iu', '', $text);
            //非严格模式，保留中文汉子，数字
        }

        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);
        return $text;
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
            '￥', '￣', '｀'
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
            '$', '~', '`'
        );
        //true 全角->半角
        return $flag ? str_replace($fullangle, $semiangle, $text) : str_replace($semiangle, $fullangle, $text);
    }

    protected function getKeywordFilter()
    {
        $filter = $this->getKernel()->getParameter('keyword.filter');
        return new $filter();
    }

    protected function getSensitiveDao()
    {
        return $this->createDao('SensitiveWord:Sensitive.SensitiveDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getBanlogDao()
    {
        return $this->createDao('SensitiveWord:Sensitive.KeywordBanlogDao');
    }
}
