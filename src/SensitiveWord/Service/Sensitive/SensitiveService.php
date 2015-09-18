<?php
namespace SensitiveWord\Service\Sensitive;

interface SensitiveService {
    /**
     * @param $text
     * @return mixed
     */
    public function scanText($text);

    public function sensitiveCheck($text, $type = '');
    
    public function findAllKeywords();
    
    public function addKeyword($keyword);
    
    public function deleteKeyword($id);
    
    public function searchkeywordsCount();
    
    public function searchKeywords($start, $limit);
    
    public function searchBanlogsCount($conditions);
    
    public function searchBanlogs($conditions, $orderBy, $start, $limit);
}
