<?php

namespace Topxia\Service\Article\Dao;

interface LocationDao
{

    public function getLocation($id);
    
    public function findLocationsByIds(array $ids);

    public function findAllLocations();

}