<?php 
namespace Custom\Service\TopLink\Dao;
interface TopLinkDao
{
    const TABLENAME = 'top_link';

    public function getTopLink($id);
    
    public function searchTopLinks($conditions, $orderBy, $start, $limit);

    public function searchTopLinkCount($conditions);
    
    public function addTopLink($topLink);

    public function updateTopLink($id,$fields);

    public function deleteTopLink($id);
}