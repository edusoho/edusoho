<?php

namespace Tests\Unit\Importer;

use Biz\BaseTestCase;
use Biz\Importer\SensitiveImporter;
use Biz\Sensitive\Dao\SensitiveDao;
use Biz\User\CurrentUser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class SensitiveImporterTest extends BaseTestCase
{
    public function testImportWhenEmptyImportDataThenReturn0()
    {
        $request = new Request([], [
            'importData' => [],
        ]);

        $import = new SensitiveImporter($this->getBiz());
        $this->assertEquals(['successCount' => 0], $import->import($request));
    }

    public function testImport()
    {
        $keyword1 = $this->createKeyword('keyword1');
        $keyword2 = $this->createKeyword('keyword2');
        $request = new Request([], [
            'importData' => [
                ['name' => $keyword1['name'], 'state' => 'banned'],
                ['name' => $keyword2['name'], 'state' => 'replaced'],
                ['name' => 'keyword3', 'state' => 'banned'],
                ['name' => 'keyword4', 'state' => 'replaced'],
            ],
        ]);

        $import = new SensitiveImporter($this->getBiz());
        $result = $import->import($request);
        $resultKeyword1 = $this->getSensitiveDao()->get($keyword1['id']);
        $resultKeyword2 = $this->getSensitiveDao()->get($keyword2['id']);

        $this->assertEquals('banned', $resultKeyword1['state']);
        $this->assertEquals('replaced', $resultKeyword2['state']);
        $this->assertEquals(['successCount' => 4], $result);
        $this->assertTrue(true);
    }

    public function testTryImportReturnTrue()
    {
        $request = new Request([], []);
        $import = new SensitiveImporter($this->getBiz());

        $this->assertTrue($import->tryImport($request));
    }

    public function testTryImportReturnFalse()
    {
        $request = new Request([], []);
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin5',
            'email' => 'admin5@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER'],
        ]);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $import = new SensitiveImporter($this->getBiz());
        $this->assertFalse($import->tryImport($request));
    }

    public function testCheckWhenDataRepeatThenReturnErrorResponse()
    {
        $importer = new SensitiveImporter($this->getBiz());
        $request = new Request([], [], [], [], [
            'excel' => new UploadedFile(__DIR__.'/File/repeat_data_sensitive_import.xls', 'repeat_data_sensitive_import.xls'),
        ]);

        $result = $importer->check($request);

        $this->assertEquals(['第1列重复，重复内容如下:<br>第4行：屏蔽敏感词<br>第5行：屏蔽敏感词<br>'], $result['errorInfo']);
    }

    public function testCheckWhenImportDataErrorThenReturnErrorResponse()
    {
        $importer = new SensitiveImporter($this->getBiz());
        $request = new Request([], [], [], [], [
            'excel' => new UploadedFile(__DIR__.'/File/error_data_sensitive_import.xls', 'error_data_sensitive_import.xls'),
        ]);

        $result = $importer->check($request);

        $this->assertEquals(['第4行的敏感词类型不正确，请检查。', '第5行的敏感词信息缺失，请检查。'], $result['errorInfo']);
    }

    public function testCheck()
    {
        $importer = new SensitiveImporter($this->getBiz());
        $request = new Request([], [], [], [], [
            'excel' => new UploadedFile(__DIR__.'/File/sensitive_import.xls', 'sensitive_import.xls'),
        ]);

        $result = $importer->check($request);

        $this->assertEquals([
            ['name' => '禁用敏感词', 'state' => 'banned'],
            ['name' => '屏蔽敏感词', 'state' => 'replaced'],
        ], $result['importData']);
    }

    protected function createKeyword($name = 'keyword', $state = 'replaced')
    {
        return $this->getSensitiveDao()->create(['name' => $name, 'state' => $state]);
    }

    /**
     * @return SensitiveDao
     */
    protected function getSensitiveDao()
    {
        return $this->createDao('Sensitive:SensitiveDao');
    }
}
