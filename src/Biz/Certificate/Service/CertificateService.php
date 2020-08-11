<?php

namespace Biz\Certificate\Service;

interface CertificateService
{
    public function get($id);

    public function getCertificateByCode($code);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function count($conditions);

    public function create($fields);
}
