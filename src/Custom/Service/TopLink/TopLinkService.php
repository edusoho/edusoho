<?php 
namespace Custom\Service\TopLink;

interface TopLinkService
{
	public function getTopLink($id);

    public function searchTopLinks(array $conditions, array $orderBy, $start, $limit);

    public function searchTopLinkCount(array $conditions);

    public function createTopLink($topLink);

    public function editTopLink($id,$fields);

    public function removeTopLink($id);

}