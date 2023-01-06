<?php

namespace AppBundle\Component\Export;

use AppBundle\Common\Exception\UnexpectedValueException;
use AppBundle\Common\FileToolkit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use ZipArchive;

class BatchExporter
{
    /**
     * @var ContainerInterface
     */
    private $container = null;

    private $names = [];

    private $conditions = [];

    private $exporters = [];

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function findExporter($names, $conditions)
    {
        if (empty($names)) {
            throw new UnexpectedValueException('exporter class could not be found');
        }

        $this->names = $names;
        $this->conditions = $conditions;

        foreach ($names as $name) {
            $this->exporters[$name] = $this->getExportFactory()->create($name, $this->conditions);
        }
    }

    public function canExport()
    {
        foreach ($this->exporters as $exporter) {
            if (!$exporter->canExport()) {
                return false;
            }
        }

        return true;
    }

    public function getCount()
    {
        $count = [];
        foreach ($this->exporters as $exporter) {
            $count[] = $exporter->getCount();
        }

        return $count;
    }

    public function export($name = '')
    {
        if (empty($name)) {
            $name = current($this->names);
        }

        if (!in_array($name, $this->names)) {
            throw new UnexpectedValueException('exporter class could not be found');
        }

        $result = $this->exporters[$name]->export();

        if (!$result['success']) {
            return $result;
        }

        $key = array_search($name, $this->names);
        if ('finish' === $result['status']) {
            $csvName = $this->writeCsv($result['fileName'], $this->generateCsvName($name));

            return [
                'fileName' => $result['fileName'],
                'csvName' => $csvName,
                'start' => 0,
                'count' => $result['count'],
                'status' => $result['status'],
                'name' => empty($this->names[$key + 1]) ? '' : $this->names[$key + 1],
                'success' => '1',
            ];
        }

        return [
            'fileName' => $result['fileName'],
            'start' => $result['start'],
            'count' => $result['count'],
            'status' => $result['status'],
            'name' => $name,
            'success' => '1',
        ];
    }

    public function exportFile($name, $fileNames, $customFileName = '')
    {
        if (empty($fileNames)) {
            return new JsonResponse(['success' => 0, 'message' => 'empty file']);
        }

        if (!is_array($fileNames)) {
            $fileNames = [$fileNames];
        }

        if (1 == count($fileNames)) {
            return $this->exportCsv($name, $fileNames[0], $customFileName);
        } else {
            return $this->exportZip($name, $fileNames, $customFileName);
        }
    }

    protected function exportCsv($name, $fileName, $customFileName)
    {
        $exportPath = $this->exportFileRootPath().basename($fileName);

        return [$exportPath, $this->transTitle($fileName, $customFileName)];
    }

    protected function exportZip($name, $fileNames, $customFileName)
    {
        $zip = new ZipArchive();

        $zipPath = $this->exportFileRootPath().$this->generateExportName();

        if (true === $zip->open($zipPath, ZipArchive::CREATE)) {
            foreach ($fileNames as $value) {
                $path = $this->exportFileRootPath().basename($value);
                if (file_exists($path)) {
                    $zip->addFile($path, $this->transTitle($value, $customFileName));
                }
            }
        } else {
            return false;
        }

        $zip->close();

        foreach ($fileNames as $value) {
            $path = $this->exportFileRootPath().basename($value);
            if (file_exists($path)) {
                FileToolkit::remove($path);
            }
        }

        $name = !empty($customFileName) ? urlencode($customFileName) : $name;
        $fileName = sprintf($name.'-(%s).zip', date('Y-n-d'));

        return [$zipPath, $fileName];
    }

    protected function transTitle($name, $customFileName = '')
    {
        $name = explode('_', $name);
        $translator = $this->container->get('translator');
        $title = !empty($customFileName) ? iconv('UTF-8', 'gb2312', $customFileName) : $translator->trans(ExportUtil::getExportCsvTitle($name[0]));

        return sprintf($title.'-(%s).csv', date('Y-n-d'));
    }

    protected function writeCsv($fileName, $csvName)
    {
        if (empty($fileName)) {
            throw new UnexpectedValueException('exporter filename could not be found');
        }
        $filePath = $this->exportFileRootPath().$fileName;
        $csvPath = $this->exportFileRootPath().$csvName;
        $contentRows = $this->getContentRows($filePath);
        $fp = fopen($csvPath, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

        foreach ($contentRows as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        $this->delete($filePath);

        return $csvName;
    }

    protected function delete($filePath)
    {
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            FileToolkit::remove($item);
        }

        FileToolkit::remove($filePath);
    }

    protected function getContentRows($filePath)
    {
        $contentRows = [];
        $data = unserialize(file_get_contents($filePath));
        foreach ($data as $item) {
            $contentRows[] = unserialize(file_get_contents($item));
        }

        return $this->handleContent($contentRows);
    }

    private function exportFileRootPath()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $rootPath = $biz['topxia.upload.private_directory'].'/tmp/';
        if (!$filesystem->exists($rootPath)) {
            $filesystem->mkdir($rootPath);
        }

        return $rootPath;
    }

    private function handleContent($content)
    {
        $data = [];
        foreach ($content as $item) {
            foreach ($item as $values) {
                $data[] = $values;
            }
        }

        return $data;
    }

    private function generateCsvName($name)
    {
        return $name.'_'.time().rand().'.csv';
    }

    private function generateExportName()
    {
        return 'export_'.time().rand().'.zip';
    }

    protected function getExportFactory()
    {
        return $this->container->get('export_factory');
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
