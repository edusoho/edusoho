<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ExportI18nKeyCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('util:export-i18n-key')
            ->addArgument('ymlPathCn', InputArgument::REQUIRED, 'yml中文文件路径')
            ->addArgument('ymlPathEn', InputArgument::REQUIRED, 'yml英文文件路径')
            ->addArgument('exportPath', InputArgument::REQUIRED, '导出文件路径')
            ->setDescription('导出国际化词条中英对照表');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = array();
        $parameters['ymlPathCn'] = $input->getArgument('ymlPathCn');
        $parameters['ymlPathEn'] = $input->getArgument('ymlPathEn');
        $parameters['exportPath'] = $input->getArgument('exportPath');
        $exporter = new KeyExporter($parameters);
        $exporter->export();
    }
}

class KeyExporter
{
    private $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    public function export()
    {
        $titles = $this->getTitles();
        $contentRows = $this->getContentRows();
        $this->exportCsv($titles, $contentRows);
    }

    //定义导出标题
    public function getTitles()
    {
        return array('词条KEY', '中文', '英文');
    }

    //获得导出正文内容
    public function getContentRows()
    {
        $ymlCnContent = Yaml::parse(file_get_contents($this->parameters['ymlPathCn']));
        $ymlEnContent = Yaml::parse(file_get_contents($this->parameters['ymlPathEn']));
        $data = array();
        foreach ($ymlCnContent as $key => $cn) {
            $en = ' ';
            if (isset($ymlEnContent[$key])) {
                $en = $ymlEnContent[$key];
            }
            $data[] = array(
                $key,
                $cn,
                $en,
            );
        }

        return $data;
    }

    public function exportCsv($titles, $contentRows)
    {
        $fp = fopen($this->parameters['exportPath'], 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($fp, $titles);

        foreach ($contentRows as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }
}
