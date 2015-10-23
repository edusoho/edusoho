<?php

namespace Topxia\Service\Taxonomy\Dao;

interface LocationDao
{

    public function getLocation($id);
    
    public function findLocationsByIds(array $ids);

    public function findAllLocations();

}