<?php

namespace AppBundle\Component\Export;

interface ExporterInterface
{
    public function export($fileName);

    /**
     * @return conditions
     *                    导出功能相关参数 $start, $filePath
     */
    public function buildParameter($conditions);

    /**
     * @return conditions
     *                    过滤，构建查询条件
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
