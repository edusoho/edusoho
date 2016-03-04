<?php
namespace Topxia\Service\File;

interface UploadFileService2
{
    public function getFile($id);

    /**
     * 获得文件基础信息
     */
    public function getThinFile($id);

    public function getFileByGlobalId($globalId);

    public function findFilesByIds(array $ids);

    public function searchFiles($conditions, $orderBy, $start, $limit);

    public function searchFilesCount($conditions);

    public function getDownloadFile($id);

    public function initUpload($params);

    public function finishedUpload($params);

    public function setFileProcessed($params);

    public function deleteFiles(array $ids);

    public function increaseFileUsedCount($id);

    public function decreaseFileUsedCount($id);

    //云接口
    //查询文件接口
    public function search($conditions, $storage);

    public function getByGlobalId($globalId);

    public function edit($globalId, $fields);

    /**
     * share file
     */
    public function addShare($sourceUserId, $targetUserId);

    public function findShareHistory($sourceUserId);

    public function findShareHistoryByUserId($sourceUserId, $targetUserId);

    public function waveUploadFile($id, $field, $diff);

    public function reconvertFile($id, $convertCallback);



}
