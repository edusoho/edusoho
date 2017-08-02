<?php

namespace AppBundle\Component\Export;

interface ExporterInterface
{
    public function export($fileName);

    public function buildCondition($conditions);

    public function getTitles();

    public function canExport();

    public function getCount();

    public function getContent($start, $limit);
}
