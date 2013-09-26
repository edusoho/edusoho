<?php

namespace Topxia\Service\Content\Dao;

interface ContentDao
{
	public function getContent($id);

	public function getContentByAlias($alias);

	public function searchContents($conditions, $orderBy, $start, $limit);

	public function searchContentCount($conditions);

	public function addContent($content);

	public function updateContent($id, $content);

	public function deleteContent($id);
}