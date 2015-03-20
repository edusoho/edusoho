<?php
namespace Topxia\Service\Content;

interface ContentService
{
	public function getContent($id);

	public function getContentByAlias($alias);

	public function searchContents($conditions, $orderBy, $start, $limit);

	public function searchContentCount($conditions);

	public function createContent($content);

	public function updateContent($id, $content);

	public function trashContent($id);

	public function deleteContent($id);

	public function isAliasAvaliable($alias);
}