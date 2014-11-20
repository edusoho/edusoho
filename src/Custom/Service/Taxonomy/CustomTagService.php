<?php
namespace Custom\Service\Taxonomy;

use Topxia\Service\Taxonomy\Tagservice as Tagservice;

interface CustomTagService extends Tagservice
{
	  public function changeTagAvatar($tagId, $filePath, array $options);
}