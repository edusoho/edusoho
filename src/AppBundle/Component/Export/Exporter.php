<?php

namespace AppBundle\Component\Export;

abstract class Exporter implements ExporterInterface
{
    protected $container;
    protected $conditions;
    protected $biz;

    public function __construct($container, $conditions)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');

        $this->conditions = $this->buildCondition($conditions);
    }

    abstract public function getTitles();

    abstract public function getContent($start, $limit);

    abstract public function canExport();

    abstract public function getCount();

    public function export($name)
    {
        if (!$this->canExport()) {
            return array(
                'success' => 0,
                'message' => 'export.not_allowed',
            );
        }
        list($start, $limit) = $this->getPageConditions();

        $fileName = empty($this->conditions['start']) ? $this->generateExportName() : $this->conditions['fileName'];

        $filePath = $this->biz['topxia.upload.private_directory'] . '/' . $fileName;

        list($data, $count) = $this->getContent(
            $start,
            $limit
        );

        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;
        $endStatus = $endPage >= $count;

        $status = $endStatus ? 'finish' : 'continue';

        return array(
            'status' => $status,
            'fileName' => $fileName,
            'start' => $endPage,
            'count' => $count,
        );
    }

    protected function addContent($data, $start, $filePath)
    {
        if ($start == 0) {
            array_unshift($data, $this->getTitles());
        }
        $partPath = $this->updateFilePaths($filePath, $start);
        file_put_contents($partPath, serialize($data), FILE_APPEND);
    }

    private function generateExportName()
    {
        return 'export_'.time().rand();
    }

    protected function updateFilePaths($path, $page)
    {
        $content = file_exists($path) ? file_get_contents($path) : '';
        $content = unserialize($content);
        $partPath = $path.$page;
        $content[] = $partPath;
        file_put_contents($path, serialize($content));

        return $partPath;
    }

    protected function getPageConditions()
    {
        $magic = $this->getSettingService()->get('magic');
        $start = isset($this->conditions['start']) ? $this->conditions['start'] : 0;
        if (empty($magic['export_limit'])) {
            $magic['export_limit'] = 1000;
        }

        return array($start, $magic['export_limit']);
    }

    public function buildCondition($conditions)
    {
        return $conditions;
    }

    public function getUser()
    {
        return $this->biz['user'];
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
