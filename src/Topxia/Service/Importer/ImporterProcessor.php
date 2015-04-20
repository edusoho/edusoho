<?php
namespace Topxia\Service\Importer;

interface ImporterProcessor 
{
    public function validateExcelFile($file);

    public function excelAnalyse($file);

    public function getExcelFieldsValue();

    public function getFieldSort();

    public function validExcelFieldValue($userData,$row,$fieldCol);

	public function checkRepeatData();

	public function arrayRepeat($array);
	
	public function checkNecessaryFields($excelFields);

	public function getUserData();

	public function tryManage($targetId);

	public function getExcelExample();

	public function getExcelInfoValidateUrl();

	public function getExcelInfoImportUrl();

	public function excelDataImporting($targetObject, $userData, $userUrl);

}