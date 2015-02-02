<?php

namespace Topxia\Service\Classroom;

interface ClassroomOrderService
{   
    public function createOrder($id);

    public function doSuccessPayOrder($id);
}