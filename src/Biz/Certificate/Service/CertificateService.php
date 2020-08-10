<?php

namespace Biz\Certificate\Service;

interface CertificateService
{
    public function get($id);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function count($conditions);
}
