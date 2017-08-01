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

        $this->conditions = $this->buildExportCondition($conditions);
    }

    abstract public function getTitles();

    abstract public function getContent($start, $limit);

    abstract public function canExport();

    abstract public function getCount();

    public function export($name)
    {
        list($start, $limit, $exportAllowCount) = $this->getPageConditions();

        if (empty($this->conditions['start'])) {
            $filePath = $this->generateExportPaths($name);
        } else {
            //第一次请求路径是根据文件名生成，第二次请求路径在请求里
            $filePath = $this->conditions['filePath'];
        }

        list($data, $count) = $this->getContent(
            $start,
            $limit
        );

        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;
        $endStatus = ($endPage >= $count) || ($endPage > $exportAllowCount);

        $status = $endStatus ? 'finish' : 'continue';

        return array(
            'status' => $status,
            'filePath' => $filePath,
            'start' => $endPage,
            'count' => $count,
            'exportAllowCount' => $exportAllowCount
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

    private function generateExportPaths($fileName)
    {
        $rootPath = $this->biz['topxia.upload.private_directory'];
        $user = $this->biz['user'];

        $md = md5($fileName.$user->getId().time());

        return $rootPath .'/'. $md;
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

        if (empty($magic['export_allow_count'])) {
            $magic['export_allow_count'] = 10000;
        }

        $limit = ($magic['export_limit'] > $magic['export_allow_count']) ? $magic['export_allow_count'] : $magic['export_limit'];

        return array($start, $limit, $magic['export_allow_count']);
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
