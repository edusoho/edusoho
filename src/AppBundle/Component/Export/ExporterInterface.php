<?php

namespace AppBundle\Component\Export;

interface ExporterInterface
{
    public function export($fileName);


    /**
     * @return array(conditions, parameter)
     */
    public function buildCondition($conditions);

    public function getTitles();

    public function canExport();

    public function getCount();

    /**
     * @return array
     */
    public function getContent($start, $limit);
}
