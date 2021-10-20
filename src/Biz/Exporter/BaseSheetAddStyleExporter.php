<?php

namespace Biz\Exporter;

use Codeages\Biz\Framework\Context\Biz;
use PHPExcel_Exception;
use PHPExcel_Writer_Exception;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseSheetAddStyleExporter
{
    protected static $logger;

    protected $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    protected $filesystem = null;

    protected $biz = null;

    protected $PHPExcel = null;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return mixed
     *               //return xxxxxxxxxxxx.xls
     */
    abstract public function getExportFileName();

    abstract public function getSortedHeadingRow();

    /**
     * @param array $params
     * @param int   $save
     *
     * @return bool
     *
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function exporter($params, $save = 1)
    {
        $privateUploadDir = $this->biz['topxia.upload.private_directory'];
        $this->filesystem = new Filesystem();
        if (!empty($save)) {
            $path = "{$privateUploadDir}/data_export";
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path, 0777);
            }
        }

        if (!empty($save) && $this->filesystem->exists($path.'/'.$this->getExportFileName())) {
            return  true;
        }
        $this->PHPExcel = new \PHPExcel();
        $this->buildExportSheetData($params);

        $objWriter = new \PHPExcel_Writer_Excel5($this->PHPExcel);
        if (empty($save)) {
            return  $objWriter;
        }
        $objWriter->save($path.'/'.$this->getExportFileName());

        return  true;
    }

    // 自定义导出格式  行高 合并单元格 样式 数据 等
    abstract public function buildExportSheetData($params);

    protected function setSheetCellValue(\PHPExcel_Worksheet $sheet, $data, $start = 2)
    {
        $i = 0;
        foreach ($this->getSortedHeadingRow() as $key => $useCol) {
            $sheet->setCellValue($this->cols[$i].$start, $key);
            $col = 1 + $start;
            foreach ($data as $key => $value) {
                $sheet->setCellValue($this->cols[$i].($key + $col), $value[$useCol]);
            }
            ++$i;
        }
    }

    /**
     * A1/A1:G2
     *
     * @param string[] $pCellCoordinates
     *
     *                            设置左对齐
     */
    protected function setHorizontalLeft($pCellCoordinates = ['A1'])
    {
        foreach ($pCellCoordinates as $pCellCoordinate) {
            $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
    }

    /**
     * A1/A1:G2
     *
     * @param string[] $pCellCoordinates
     *
     *                            设置水平居中
     */
    protected function setHorizontalCenter($pCellCoordinates = ['A1'])
    {
        foreach ($pCellCoordinates as $pCellCoordinate) {
            $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
    }

    /**
     * A1/A1:G2
     *
     * @param string[] $pCellCoordinates
     *
     *                            设置垂直居中
     */
    protected function setVerticalCenter($pCellCoordinates = ['A1'])
    {
        foreach ($pCellCoordinates as $pCellCoordinate) {
            $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
    }

    /**
     *  A1/A1:G2
     *
     * @param string[] $pCellCoordinates
     * @param int      $size
     *
     *                            设置大小
     */
    protected function setSize($pCellCoordinates, $size = 16)
    {
        foreach ($pCellCoordinates as $pCellCoordinate) {
            $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getFont()->setSize($size);
        }
    }

    /**
     *  A1/A1:G2
     *
     * @param string $pCellCoordinate
     * @param string $color           'FFadafb1'
     *
     *                            设置背景色
     */
    protected function setBackground($pCellCoordinate, $color)
    {
        $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->getStartColor()->setARGB($color);
    }

    /**
     *  A
     *
     * @param string[] $columns
     * @param int      $size
     *
     *                            设置单元格宽度
     */
    protected function setWidth($columns, $size = 20)
    {
        foreach ($columns as $column) {
            $this->PHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($size);
        }
    }

    /**
     * @param int $size
     *
     *                            设置默认行高
     */
    protected function setDefaultRowHeight($size = 20)
    {
        $this->PHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight($size);
    }

    /**
     *  A1/A1:G2
     *
     * @param string[] $pCellCoordinates
     *
     *   设置加粗
     */
    protected function setBold($pCellCoordinates)
    {
        foreach ($pCellCoordinates as $pCellCoordinate) {
            $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->getFont()->setBold(true);
        }
    }

    /**
     * @param $pCellCoordinate
     *
     *    添加边框，加粗
     */
    protected function setBorders($pCellCoordinate)
    {
        $style_array = [
            'borders' => [
                'allborders' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                ],
            ], ];
        $this->PHPExcel->getActiveSheet()->getStyle($pCellCoordinate)->applyFromArray($style_array);
    }

    /**
     * @param $pCellCoordinate
     *
     * @throws PHPExcel_Exception
     *                            合并单元格
     */
    protected function mergeCells($pCellCoordinate)
    {
        $this->PHPExcel->getActiveSheet()->mergeCells($pCellCoordinate);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
