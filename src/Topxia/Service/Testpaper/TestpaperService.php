<?php
namespace Topxia\Service\Testpaper;

interface TestpaperService
{
    public function getTestpaper($id);

    public function searchTestpapers($conditions, $sort, $start, $limit);

    public function searchTestpapersCount($conditions);

    public function publishTestpaper($id);

    public function closeTestpaper($id);

    public function deleteTestpaper($id);

    public function deleteTestpaperByIds(array $ids);

    public function buildTestpaper($testpaper, $builder, $builderOptions);

    public function rebuildTestpaper($testpaperId, $builder, $builderOptions);

    public function addItem($testpaperId, $questionId, $afterItemId = null);

    public function replaceItem($testpaperId, $itemId, $questionId);

}