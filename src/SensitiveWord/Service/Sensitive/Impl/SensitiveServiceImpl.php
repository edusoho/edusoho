<?php
namespace SensitiveWord\Service\Sensitive\Impl;

use SensitiveWord\Service\Sensitive\SensitiveService;
use Topxia\Service\Common\BaseService;
class SensitiveServiceImpl extends BaseService implements SensitiveService
{
    const file = '../app/logs/post-dely.log'; 
    public function sensitiveCheck($text,  $type = '')
    {
        if(!empty($type)){
            $postStatus = $this->getUserLevelService()->checkUserStatusByType($type);

            if($postStatus){ 
               $user = $this->getCurrentUser();
               file_put_contents(self::file, "\t\t".$text."\t".$user['id']."\t".$user['nickname']. "\t".$type."\n", FILE_APPEND); 
               return array('success' => false, 'message' => '休息一下,去看视频吧，你今天已经到达提交上限了');
            } 
        }
        //预处理内容
        $text = strip_tags($text);
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
        
        $pattern = '/(' . implode('|', $keywords) . ')/';
        $matched = preg_match($pattern, $text, $match);
        if (!$matched) {
            return false;
        }

        $bannedKeyword = $this->getSensitiveDao()->getKeywordByName($match[1]);
        if (empty($bannedKeyword)) {
            return false;
        }
        
        $currentUser = $this->getCurrentUser();
        
        $env = $this->getEnvVariable();
        $banlog = array(
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'text' => $text,
            'userId' => $currentUser ? $currentUser['id'] : 0,
            'ip' => 0,
            'createdTime' => time() ,
        );
        
        $this->getBanlogDao()->addBanlog($banlog);
        
        $this->getSensitiveDao()->waveBannedNum($bannedKeyword['id'], 1);

        return array('success' => false, 'message' => '非法输入');
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
        
        $pattern = '/(' . implode('|', $keywords) . ')/';
        $matched = preg_match($pattern, $text, $match);
        if (!$matched) {
            return false;
        }
        
        $bannedKeyword = $this->getSensitiveDao()->getKeywordByName($match[1]);
        if (empty($bannedKeyword)) {
            return false;
        }
        
        $currentUser = $this->getCurrentUser();
        
        $env = $this->getEnvVariable();
        $banlog = array(
            'keywordId' => $bannedKeyword['id'],
            'keywordName' => $bannedKeyword['name'],
            'text' => $text,
            'userId' => $currentUser ? $currentUser['id'] : 0,
            'ip' => 0,
            'createdTime' => time() ,
        );
        
        $this->getBanlogDao()->addBanlog($banlog);
        
        $this->getSensitiveDao()->waveBannedNum($bannedKeyword['id'], 1);
        
        return $match[1];
    }
    
    public function findAllKeywords()
    {
        return $this->getSensitiveDao()->findAllKeywords();
    }
    
    public function addKeyword($keyword)
    {
        $keyword = array(
            'name' => $keyword,
            'createdTime' => time()
        );
        return $this->getSensitiveDao()->addKeyword($keyword);
    }
    
    public function deleteKeyword($id)
    {
        return $this->getSensitiveDao()->deleteKeyword($id);
    }
    
    public function searchkeywordsCount()
    {
        return $this->getSensitiveDao()->searchkeywordsCount();
    }
    
    public function searchKeywords($start, $limit)
    {
        return $this->getSensitiveDao()->searchKeywords($start, $limit);
    }
    
    public function searchBanlogsCount($conditions)
    {
        return $this->getBanlogDao()->searchBanlogsCount($conditions);
    }
    
    public function searchBanlogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getBanlogDao()->searchBanlogs($conditions, $orderBy, $start, $limit);
    }
    
    /** @param 	
     *移除不可见字符
     *
     */
    private function plainTextFilter($text, $strictmodel = false)
    {
        if ($strictmodel) {
            $text = preg_replace('/[^\x{4e00}-\x{9fa5}]/iu', '', $text);
             //严格模式，仅保留中文 
            
        } 
        else {
            $text = preg_replace('/[^a-zA-Z0-9\x{4e00}-\x{9fa5}]/iu', '', $text);
             //非严格模式，保留中文汉子，数字
            
        }
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);
        return $text;
    }
    
    /** @param $text 要转换的字符串
     * @param $flag 全角半角转换 true:半角， false:全角, 默认转换为半角
     */
    private function semiangleTofullangle($text, $flag = true)
    {
        $fullangle = Array(
	        '０' , '１' , '２' , '３' , '４' ,
	        '５' , '６' , '７' , '８' , '９' ,
	        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
	        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
	        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
	        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
	        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
	        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
	        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
	        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
	        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
	        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
	        'ｙ' , 'ｚ' , '－' , '　' , '：' ,
	        '．' , '，' , '／' , '％' , '＃' ,
	        '！' , '＠' , '＆' , '（' , '）' ,
	        '＜' , '＞' , '＂' , '＇' , '？' ,
	        '［' , '］' , '｛' , '｝' , '＼' ,
	        '｜' , '＋' , '＝' , '＿' , '＾' ,
	        '￥' , '￣' , '｀'
	    );
	    $semiangle = Array( // 半角
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
	        '<', '>', '"', '\'','?',
	        '[', ']', '{', '}', '\\',
	        '|', '+', '=', '_', '^',
	        '$', '~', '`'
	    );
        //true 全角->半角
        return $flag ? str_replace($fullangle, $semiangle, $text) : str_replace($semiangle, $fullangle, $text);
    }
    
    protected function getSensitiveDao()
    {
        return $this->createDao('SensitiveWord:Sensitive.SensitiveDao');
    }
    
    protected function getBanlogDao()
    {
        return $this->createDao('SensitiveWord:Sensitive.KeywordBanlogDao');
    }
    protected function getUserLevelService()
    {
        return $this->createService('SensitiveWord:UserLevel.UserLevelService');
    }
    protected function getThreadService()
    {
        return $this->createService('Group.ThreadService');
    }
}
