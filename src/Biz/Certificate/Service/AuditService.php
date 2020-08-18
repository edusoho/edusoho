<?php


namespace Biz\Certificate\Service;


interface AuditService
{
    public function get($id);

    public function count($conditions);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function passCertificate($id, $fields);

    public function rejectCertificate($id, $fields);

}