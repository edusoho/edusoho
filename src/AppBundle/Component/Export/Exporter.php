<?php

namespace AppBundle\Component\Export;

abstract class Exporter implements ExporterInterface
{
    protected $container;
    protected $conditions;
    protected $parameter;

    public function __construct($container, $conditions)
    {
        $this->container = $container;

        $this->parameter = $this->buildParameter($conditions);
        $this->conditions = $this->buildCondition($conditions);
    }

    abstract public function getTitles();

    abstract public function getContent($start, $limit);

    abstract public function canExport();

    abstract public function getCount();

    abstract public function buildCondition($conditions);

    public function export($name)
    {
        list($start, $limit) = $this->getPageConditions();

        if (empty($this->parameter['start'])) {
            $filePath = $this->generateExportPaths($name);
        } else {
            //第一次请求路径是根据文件名生成，第二次请求路径在请求里
            $filePath = $this->parameter['filePath'];
        }

        $data = $this->getContent($start, $limit);

        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;

        $count = $this->getCount();
        $endStatus = $endPage >= $count;

        $status = $endStatus ? 'finish' : 'continue';

        return array(
            'status' => $status,
            'filePath' => $filePath,
            'start' => $endPage,
            'count' => $count,
        );
    }

    public function buildParameter($conditions)
    {
        $parameter = array();
        $start = isset($conditions['start']) ? $conditions['start'] : 0;
        $filePath = isset($conditions['filePath']) ? $conditions['filePath'] : '';

        $parameter['start'] = $start;
        $parameter['filePath'] = $filePath;

        return $parameter;
    }

    protected function addContent($data, $start, $filePath)
    {
        if ($start == 0) {
            array_unshift($data, $this->transTitles());
        }
        $partPath = $this->updateFilePaths($filePath, $start);
        file_put_contents($partPath, serialize($data), FILE_APPEND);
    }

    private function generateExportPaths($fileName)
    {
        $biz = $this->getBiz();

        $rootPath = $biz['topxia.upload.private_directory'];
        $user = $biz['user'];

        $md = md5($fileName.$user->getId().time());

        return $rootPath.'/'.$md;
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
        if (empty($magic['export_limit'])) {
            $magic['export_limit'] = 1000;
        }

        return array($this->parameter['start'], $magic['export_limit']);
    }

    private function transTitles()
    {
        $translator = $this->container->get('translator');
        $titles = $this->getTitles();
        foreach ($titles as &$title) {
            $title = $translator->trans($title);
        }
        unset($translator);

        return $titles;
    }

    public function getUser()
    {
        $biz = $this->getBiz();

        return $biz['user'];
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
