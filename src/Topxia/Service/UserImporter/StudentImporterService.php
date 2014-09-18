<?php
namespace Topxia\Service\UserImporter;


interface UserImporterService
{
	public function importStudentByOverwrite();

	public function importStudentByIgnoreError();

}
