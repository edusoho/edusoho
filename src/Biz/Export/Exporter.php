<?php

namespace Biz\Export;

use Codeages\Biz\Framework\Context\Biz;

abstract class Exporter implements ExporterInterface
{
    protected $biz;
    protected $conditions;

    final public function __construct(Biz $biz, $conditions)
    {
        $this->biz = $biz;
        $this->conditions = $conditions;
    }

    abstract public function getTitles();

    abstract public function getExportContent($start, $limit);

    public function getPreResult($fileName)
    {
        list($start, $limit, $exportAllowCount) = $this->getPageConditions();
        $filePath = $this->addFileTitle($fileName);

        list($data, $count) = $this->getExportContent(
            $start,
            $limit
        );

        $content = implode("\r\n", $data);
        file_put_contents($filePath, $content."\r\n", FILE_APPEND);

        $endPage = $start + $limit;

        $endStatus = ($endPage >= $count) || ($endPage > $exportAllowCount);

        $status = $endStatus ? 'export' : 'getData';

        return array(
            'status' => $status,
            'filePath' => $filePath,
            'start' => $endPage,
        );
    }

    protected function addFileTitle($fileName)
    {
        if (!empty($this->conditions['start'])) {
            return ;
        }
        $title = $this->getTitles();

        $content = implode(',' ,$title);

        if (empty($this->conditions['filePath'])){
            $rootPath = $this->biz['topxia.upload.private_directory'];
            $user = $this->biz['user'];
            $filePath = $rootPath.'/export_content_'.$fileName.'_'.$user->getId().time().'.txt';
        } else {
            $filePath =  $this->conditions['filePath'];
        }

        file_put_contents($filePath, $content."\r\n", FILE_APPEND);

        return $filePath;
    }

    protected function getPageConditions()
    {
        $magic = $this->getSettingService()->get('magic');
        $start = isset($this->conditions['start']) ? $this->conditions['start'] : 0;
        if (empty($magic['export_limit'])) {
            $magic['export_limit'] = 1000;
        }

        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        $limit = ($magic['export_limit'] > $magic['export_allow_count']) ? $magic['export_allow_count'] : $magic['export_limit'];

        return array($start, $limit, $magic['export_allow_count']);
    }
}