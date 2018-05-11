<?php

namespace AppBundle\Component\Export;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class Exporter implements ExporterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $conditions;
    protected $parameter;

    public function __construct($container, $conditions)
    {
        $this->container = $container;

        $this->parameter = $this->buildParameter($conditions);
        $this->conditions = $this->buildCondition($conditions);
    }

    //定义导出标题
    abstract public function getTitles();

    //获得导出正文内容
    abstract public function getContent($start, $limit);

    //下载权限判断
    abstract public function canExport();

    //获得导出总条数
    abstract public function getCount();

    //构建查询条件
    abstract public function buildCondition($conditions);

    public function export($name = '')
    {
        if (!$this->canExport()) {
            return array(
                'success' => 0,
                'message' => 'export.not_allowed',
            );
        }
        list($start, $limit) = $this->getPageConditions();

        $fileName = empty($this->parameter['start']) ? $this->generateExportName() : $this->parameter['fileName'];
        $filePath = $this->exportFileRootPath().$fileName;

        $data = $this->getContent($start, $limit);

        $this->addContent($data, $start, $filePath);

        $endPage = $start + $limit;

        $count = $this->getCount();

        $endStatus = $endPage >= $count;

        $status = $endStatus ? 'finish' : 'continue';

        return array(
            'status' => $status,
            'fileName' => $fileName,
            'start' => $endPage,
            'count' => $count,
            'success' => '1',
        );
    }

    //获得导出分页参数
    public function buildParameter($conditions)
    {
        $parameter = array();
        $start = isset($conditions['start']) ? $conditions['start'] : 0;
        $fileName = isset($conditions['fileName']) ? basename($conditions['fileName']) : '';

        $parameter['start'] = $start;
        $parameter['fileName'] = $fileName;

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
        $magic = is_array($magic) ? $magic : array();
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

    private function exportFileRootPath()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $rootPath = $biz['topxia.upload.private_directory'].'/';
        if (!$filesystem->exists($rootPath)) {
            $filesystem->mkdir($rootPath);
        }

        return  $rootPath;
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
