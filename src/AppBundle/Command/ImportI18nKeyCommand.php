<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Inline;

class ImportI18nKeyCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('util:import-i18n-key')
            ->addArgument('xlsPathCn', InputArgument::REQUIRED, 'xls中文翻译文件路径')
            ->setDescription('导入国际化词条');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = array();
        $parameters['xlsPathCn'] = $input->getArgument('xlsPathCn');
        $exporter = new KeyImporter();
        $exporter->importer($parameters);
    }
}

class KeyImporter
{
    public function importer($parameters)
    {
        $objPHPExcel = \PHPExcel_IOFactory::load($parameters['xlsPathCn']);
        $sheetCount = $objPHPExcel->getSheetCount();

        $data = array();
        for ($sheet = 0; $sheet < $sheetCount; ++$sheet) {
            $result = array();
            $currentSheet = $objPHPExcel->setActiveSheetIndex($sheet);
            $title = $currentSheet->getTitle();
            $highestColumn = $currentSheet->getHighestColumn();
            $highestRow = $currentSheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; ++$row) {
                $currentRawData = $this->buildCurrentRawData($currentSheet, $row, $highestColumn);
                $result[] = $currentRawData;
            }
            $data[$title] = $result;
        }

        foreach ($data  as $key => $value) {
            $path = $this->getMessageTypeByTitle($key);
            if (!empty($path)) {
                file_put_contents($path, $this->dump($data[$key]));
            }
        }
    }

    public function dump($input, $inline = 2, $indent = 0, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        $output = '';
        $prefix = $indent ? str_repeat('', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix.Inline::dump($input, $exceptionOnInvalidType, $objectSupport);
        } else {
            $isAHash = Inline::isHash($input);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s',
                        $prefix,
                        $isAHash ? Inline::dump($key, $exceptionOnInvalidType, $objectSupport).':' : '',
                        $willBeInlined ? ' ' : '',
                        $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + 2, $exceptionOnInvalidType, $objectSupport)
                    ).($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }

    protected function buildCurrentRawData(\PHPExcel_Worksheet $currentSheet, $currentRow, $highestColumn)
    {
        $highestColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $data = array();

        for ($i = 0; $i < $highestColumn; ++$i) {
            $value = $currentSheet->getCellByColumnAndRow($i, $currentRow)->getValue();
            if (!empty($value) && $value != null) {
                $data[$i] = $value;
            }
        }

        $result[$data[0]] = empty($data[2]) ? '' : $data[2];

        return $result;
    }

    protected function getMessageTypeByTitle($title)
    {
        switch ($title) {
            case 'message':
                return 'app/Resources/translations/messages.en.yml';
            case 'js':
                return 'app/Resources/translations/js.en.yml';
            case '积分插件-message':
                return 'plugins/RewardPointPlugin/Resources/translations/messages.en.yml';
            case '积分插件-js':
                return 'plugins/RewardPointPlugin/Resources/translations/js.en.yml';
            case '积分抵现插件-message':
                return 'plugins/RewardPointConsumptionPlugin/Resources/translations/messages.en.yml';
            case '积分抵现-js':
                return 'plugins/RewardPointConsumptionPlugin/Resources/translations/js.en.yml';
            default:
                return '';
        }
    }
}
