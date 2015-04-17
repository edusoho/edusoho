<?php
namespace Topxia\Service\Importer;

interface ImporterProcessor 
{
    public function validateExcelFile($file);

    public function excelAnalyse($file);

    public function getRowTotal($excelWorksheet);

    public function getColTotal($excelWorksheet);

    public function getExcelFieldsValue($objWorksheet, $colTotal, $row);

    public function validExcelFieldValue($userData,$row,$fieldCol);

	public function checkRepeatData($fieldSort,$highestRow,$excelWorksheet);

	public function arrayRepeat($array);
	
	public function checkNecessaryFields($excelFields);

	public function getUserData($objWorksheet, $rowAndCol, $fieldSort);

}