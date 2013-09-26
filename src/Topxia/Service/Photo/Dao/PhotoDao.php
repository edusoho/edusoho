<?php

namespace Topxia\Service\Photo\Dao;

interface PhotoDao
{

    public function getPhoto($id);

    public function findPhotoByIds(array $ids);

    public function searchPhotos($conditions, $orderBy, $start, $limit);

    public function searchPhotoCount($conditions);

    public function addPhoto($activity);

    public function updatePhoto($id, $fields);

    public function deletePhoto($id);

}