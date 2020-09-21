<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
<<<<<<< HEAD
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);
=======
    public function getEventByActionAndLocation($action, array $location);
>>>>>>> ab44444462c5817572ced9bbd9c72e66f0f72aec
}
