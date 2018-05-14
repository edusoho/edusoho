<?php

namespace Tests\Unit\Content\Service;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileServiceTest extends BaseTestCase
{
    public function testGetFile()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'groupId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getFileService()->getFile(111);

        $this->assertEquals(array('id' => 111, 'groupId' => 111), $result);
    }

    public function testGetFiles()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'find',
                    'returnValue' => array(array('id' => 111, 'groupId' => 111)),
                    'withParams' => array(0, 5),
                ),
                array(
                    'functionName' => 'findByGroupId',
                    'returnValue' => array(array('id' => 111, 'groupId' => 111)),
                    'withParams' => array(111, 0, 5),
                ),
            )
        );
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array(),
                    'withParams' => array('code'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array('id' => 111),
                    'withParams' => array('code'),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getFileService()->getFiles(null, 0, 5);
        $result2 = $this->getFileService()->getFiles('code', 0, 5);
        $result3 = $this->getFileService()->getFiles('code', 0, 5);

        $this->assertEquals(array(array('id' => 111, 'groupId' => 111)), $result1);
        $this->assertEquals(array(), $result2);
        $this->assertEquals(array(array('id' => 111, 'groupId' => 111)), $result3);
    }

    public function testGetFileCount()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'countAll',
                    'returnValue' => 5,
                ),
                array(
                    'functionName' => 'countByGroupId',
                    'returnValue' => 3,
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array(),
                    'withParams' => array('code'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array('id' => 111),
                    'withParams' => array('code'),
                    'runTimes' => 1,
                ),
            )
        );
        $result1 = $this->getFileService()->getFileCount();
        $result2 = $this->getFileService()->getFileCount('code');
        $result3 = $this->getFileService()->getFileCount('code');

        $this->assertEquals(5, $result1);
        $this->assertEquals(0, $result2);
        $this->assertEquals(3, $result3);
    }

    public function testUploadFile()
    {
        $sourceFile = __DIR__.'/Fixtures/test.gif';
        $testFile = __DIR__.'/Fixtures/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getFileService()->uploadFile('tmp', $file);
        $this->assertTrue(file_exists($fileRecord['file']->getRealPath()));
        unlink($fileRecord['file']->getRealPath());
    }

    public function testAddFile()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $sourceFile = __DIR__.'/Fixtures/test.gif';
        $testFile = __DIR__.'/Fixtures/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array('id' => 111, 'public' => 1, 'code' => 'code'),
                    'withParams' => array('code'),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $this->getFileService()->addFile('code', $file);

        $this->assertEquals(111, $result['groupId']);
    }

    public function testDeleteFile()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('uri' => 'public://code/2017/11-14/10264552570c840243.gif'),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'deleteByUri',
                    'withParams' => array('public://code/2017/11-14/10264552570c840243.gif'),
                ),
            )
        );
        $this->getFileService()->deleteFile(111);
        $this->getFileDao()->shouldHaveReceived('deleteByUri');
    }

    public function testDeleteFileByUri()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'deleteByUri',
                    'withParams' => array('public://code/2017/11-14/10264552570c840243.gif'),
                ),
            )
        );
        $this->getFileService()->deleteFileByUri('public://code/2017/11-14/10264552570c840243.gif');
        $this->getFileDao()->shouldHaveReceived('deleteByUri');
    }

    public function testGetFileObject()
    {
        $sourceFile = __DIR__.'/Fixtures/test.gif';
        $testFile = __DIR__.'/Fixtures/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getFileService()->uploadFile('tmp', $file);
        $result = $this->getFileService()->getFileObject(1);

        $this->assertEquals('image/gif', $result->getMimeType());
    }

    public function testParseFileUri()
    {
        $result = $this->getFileService()->parseFileUri('public://code/2017/11-14/10264552570c840243.gif');

        $this->assertEquals('public', $result['access']);
    }

    public function testGetFileGroup()
    {
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'name' => 'name'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getFileService()->getFileGroup(111);

        $this->assertEquals(array('id' => 111, 'name' => 'name'), $result);
    }

    public function testGetFileGroupByCode()
    {
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'returnValue' => array('id' => 111, 'code' => 'code'),
                    'withParams' => array('code'),
                ),
            )
        );
        $result = $this->getFileService()->getFileGroupByCode('code');

        $this->assertEquals(array('id' => 111, 'code' => 'code'), $result);
    }

    public function testGetAllFileGroups()
    {
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'findAll',
                    'returnValue' => array(array('id' => 111, 'code' => 'code')),
                ),
            )
        );
        $result = $this->getFileService()->getAllFileGroups();

        $this->assertEquals(array(array('id' => 111, 'code' => 'code')), $result);
    }

    public function testAddFileGroup()
    {
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 111, 'code' => 'code'),
                    'withParams' => array(array('code' => 'code')),
                ),
            )
        );
        $result = $this->getFileService()->addFileGroup(array('code' => 'code'));

        $this->assertEquals(array('id' => 111, 'code' => 'code'), $result);
    }

    public function testDeleteFileGroup()
    {
        $this->mockBiz(
            'Content:FileGroupDao',
            array(
                array(
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getFileService()->deleteFileGroup(111);

        $this->assertEquals(1, $result);
    }

    public function testGetFilesByIds()
    {
        $this->mockBiz(
            'Content:FileDao',
            array(
                array(
                    'functionName' => 'findByIds',
                    'returnValue' => array(array('id' => 11, 'groupId' => 11)),
                    'withParams' => array(array(11, 22)),
                ),
            )
        );
        $result = $this->getFileService()->getFilesByIds(array(11, 22));

        $this->assertEquals(array('id' => 11, 'groupId' => 11), $result[11]);
    }

    public function testGetImgFileMetaInfo()
    {
        $sourceFile = __DIR__.'/Fixtures/test.gif';
        $testFile = __DIR__.'/Fixtures/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getFileService()->uploadFile('tmp', $file);
        $result = $this->getFileService()->getImgFileMetaInfo(1, 800, 800);

        $this->assertEquals(800, $result[2]->getWidth());
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return FileDao
     */
    protected function getFileDao()
    {
        return $this->createDao('Content:FileDao');
    }
}
