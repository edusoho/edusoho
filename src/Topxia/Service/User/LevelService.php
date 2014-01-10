<?php
namespace Topxia\Service\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface LevelService
{
	public function getLevel($id);

    public function createLevel($level);

    public function searchLevelsCount($conditions);

    public function searchLevels($conditions,$start,$limit);

    public function deleteLevel($id);

    public function updateLevel($id,$fields);

    public function getLevelByName($name);

    public function sortLevels(array $id);

    public function isLevelNameAvailable($name, $exclude=null);
}