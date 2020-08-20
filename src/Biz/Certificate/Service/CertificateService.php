<?php

namespace Biz\Certificate\Service;

interface CertificateService
{
    public function get($id);

    public function getCertificateByCode($code);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function count($conditions);

    public function create($fields);

    public function update($id, $fields);

    public function publishCertificate($id);

    public function closeCertificate($id);

    public function delete($id);

    public function findByIds(array $ids = []);

    public function searchUserAvailableCertificates($userId, $nameLike = '', $start, $limit);

    public function countUserAvailableCertificates($userId, $nameLike = '');
}
