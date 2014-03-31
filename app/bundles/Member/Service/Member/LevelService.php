<?php
namespace Member\Service\Member;

interface LevelService
{
	public function getLevel($id);

    public function getLevelByName($name);

    public function createLevel($level);

    public function findLevelsBySeq($seq, $start, $limit);

    public function findEnabledLevels();

    public function searchLevelsCount($conditions);

    public function searchLevels($conditions,$start,$limit);

    public function deleteLevel($id);

    public function updateLevel($id,$fields);

    public function sortLevels(array $ids);

    public function onLevel($id);

    public function offLevel($id);
}