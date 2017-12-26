<?php

namespace Tests\Unit\File;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\File\Service\Impl\LocalFileImplementorImpl;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LocalFileImplementorTest extends BaseTestCase
{
    public function testGetFile()
    {
        $file = array(
            'hashId' => 'test-1/test',
        );
        $resultFile = $this->getLocalFileImplementor()->getFile($file);
        $this->assertEquals('', $resultFile['webpath']);
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $filepath = $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
        $this->assertEquals($filepath, $resultFile['fullpath']);
    }

    public function testGetFullFile()
    {
        $file = array(
            'hashId' => 'test-1/test',
        );
        $resultFile = $this->getLocalFileImplementor()->getFullFile($file);
        $this->assertEquals('', $resultFile['webpath']);
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $filepath = $baseDirectory.DIRECTORY_SEPARATOR.$file['hashId'];
        $this->assertEquals($filepath, $resultFile['fullpath']);
    }

    public function testAddFile()
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $tmpPath = $baseDirectory.DIRECTORY_SEPARATOR.'tmp';
        $trueFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtesttest/1/test.jpg';
        $tmpFilePath = $tmpPath.DIRECTORY_SEPARATOR.'tmp.jpg';
        FileToolkit::remove($tmpPath);
        mkdir($tmpPath, 0777, true);
        file_put_contents($tmpFilePath, 'test12345');
        $uploadFile = $this->getLocalFileImplementor()->addFile('materiallibtest', '1', array(), new UploadedFile($tmpFilePath, $trueFilePath, null, filesize($tmpFilePath), UPLOAD_ERR_OK, 1));
        $content = file_get_contents($baseDirectory.DIRECTORY_SEPARATOR.$uploadFile['hashId']);
        $this->assertEquals('test12345', $content);
        FileToolkit::remove($baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest');
        FileToolkit::remove($tmpPath);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage 该文件格式，不允许上传。
     */
    public function testAddFileWithServiceException()
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $tmpPath = $baseDirectory.DIRECTORY_SEPARATOR.'tmp';
        $trueFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest/1/test.unknown';
        $tmpFilePath = $tmpPath.DIRECTORY_SEPARATOR.'tmp.unknown';
        FileToolkit::remove($tmpPath);
        mkdir($tmpPath, 0777, true);
        file_put_contents($tmpFilePath, 'test12345');
        $this->getLocalFileImplementor()->addFile('materiallibtest', '1', array(), new UploadedFile($tmpFilePath, $trueFilePath, null, filesize($tmpFilePath), UPLOAD_ERR_OK, 1));
    }

    public function testSaveConvertResult()
    {
        $result = $this->getLocalFileImplementor()->saveConvertResult(null, array());
        $this->assertNull($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage 本地文件暂不支持转换
     */
    public function testConvertFile()
    {
        $this->getLocalFileImplementor()->convertFile(null, '');
    }

    public function testUpdateFile()
    {
        $result = $this->getLocalFileImplementor()->updateFile(null, array());
        $this->assertNull($result);
    }

    public function testMakeUploadParams()
    {
        $result = $this->getLocalFileImplementor()->makeUploadParams(array('user' => $this->getCurrentUser()->getId(), 'defaultUploadUrl' => '/test'));
        $this->assertEquals('local', $result['storage']);
        $this->assertEquals('/test', $result['url']);
        $this->assertNotEmpty($result['postParams']['token']);
    }

    public function testReconvert()
    {
        $result = $this->getLocalFileImplementor()->reconvert('1', array());
        $this->assertNull($result);
    }

    public function testGetUploadAuth()
    {
        $result = $this->getLocalFileImplementor()->getUploadAuth(array());
        $this->assertNull($result);
    }

    public function testReconvertFile()
    {
        $result = $this->getLocalFileImplementor()->reconvertFile(null, '');
        $this->assertNull($result);
    }

    public function testFindFiles()
    {
        $result = $this->getLocalFileImplementor()->findFiles(null, array());
        $this->assertNull($result);
    }

    public function testprepareUpload()
    {
        $result = $this->getLocalFileImplementor()->prepareUpload(array('targetId' => 1, 'targetType' => 'materiallibtest'));
        $this->assertEquals('local', $result['storage']);
        $this->assertEquals('materiallibtest', $result['targetType']);
        $this->assertEquals(1, $result['targetId']);
    }

    public function testMoveFile()
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $tmpPath = $baseDirectory.DIRECTORY_SEPARATOR.'tmp';
        $tmpFilePath = $tmpPath.DIRECTORY_SEPARATOR.'tmp.jpg';
        FileToolkit::remove($tmpPath);
        mkdir($tmpPath, 0777, true);
        file_put_contents($tmpFilePath, 'test12345');
        $moveFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest/1/test1.jpg';
        $this->getLocalFileImplementor()->moveFile('materiallibtest', '1', new UploadedFile($tmpFilePath, $moveFilePath, null, filesize($tmpFilePath), UPLOAD_ERR_OK, 1), array('hashId' => 'materiallibtest/1/testMoved.jpg'));
        $content = file_get_contents($baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest/1/testMoved.jpg');
        $this->assertEquals('test12345', $content);
        FileToolkit::remove($baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest');
        FileToolkit::remove($tmpPath);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage 该文件格式，不允许上传。
     */
    public function testMoveFileWithServiceException()
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $tmpPath = $baseDirectory.DIRECTORY_SEPARATOR.'tmp';
        $trueFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest/1/test.jpg';
        $tmpFilePath = $tmpPath.DIRECTORY_SEPARATOR.'tmp.jpg';
        FileToolkit::remove($tmpPath);
        mkdir($tmpPath, 0777, true);
        file_put_contents($tmpFilePath, 'test12345');
        $moveFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest/1/test1.unknown';
        $this->getLocalFileImplementor()->moveFile('materiallibtest', '1', new UploadedFile($tmpFilePath, $moveFilePath, null, filesize($tmpFilePath), UPLOAD_ERR_OK, 1), array('hashId' => 'materiallibtest/1/testMoved.jpg'));
    }

    public function testReconvertOldFile()
    {
        $result = $this->getLocalFileImplementor()->reconvertOldFile(null, '');
        $this->assertNull($result);
    }

    public function testFinishedUpload()
    {
        $result = $this->getLocalFileImplementor()->finishedUpload(null, array());
        $this->assertEquals(array('success' => true, 'convertStatus' => 'success'), $result);
    }

    public function testResumeUpload()
    {
        $result = $this->getLocalFileImplementor()->resumeUpload('', array());
        $this->assertNull($result);
    }

    public function testGetDownloadFile()
    {
        $result = $this->getLocalFileImplementor()->getDownloadFile(null, false);
        $this->assertNull($result);
    }

    public function testDeleteFile()
    {
        $baseDirectory = $this->biz['topxia.disk.local_directory'];
        $tmpPath = $baseDirectory.DIRECTORY_SEPARATOR.'tmp';
        $trueFilePath = $baseDirectory.DIRECTORY_SEPARATOR.'materiallibtesttest/1/test.jpg';
        $tmpFilePath = $tmpPath.DIRECTORY_SEPARATOR.'tmp.jpg';
        FileToolkit::remove($tmpPath);
        mkdir($tmpPath, 0777, true);
        file_put_contents($tmpFilePath, 'test12345');
        $uploadFile = $this->getLocalFileImplementor()->addFile('materiallibtest', '1', array(), new UploadedFile($tmpFilePath, $trueFilePath, null, filesize($tmpFilePath), UPLOAD_ERR_OK, 1));
        $result = $this->getLocalFileImplementor()->deleteFile($uploadFile);
        $this->assertEquals(array('success' => true), $result);
        FileToolkit::remove($baseDirectory.DIRECTORY_SEPARATOR.'materiallibtest');
        FileToolkit::remove($tmpPath);
    }

    public function testSearch()
    {
        $this->mockBiz('File:UploadFileDao', array(
            array('functionName' => 'search', 'returnValue' => array(1 => array('id' => 1))),
        ));
        $result = $this->getLocalFileImplementor()->search(array('start' => 0, 'limit' => 10));
        $this->assertEquals(1, count($result));

        $biz = $this->getBiz();
        unset($biz['@File:UploadFileDao']);
    }

    public function testGetFileByGlobalId()
    {
        $result = $this->getLocalFileImplementor()->getFileByGlobalId('1');
        $this->assertNull($result);
    }

    public function testInitUpload()
    {
        $result = $this->getLocalFileImplementor()->initUpload(array('userId' => $this->getCurrentUser()->getId()));
        $this->assertEquals('local', $result['uploadMode']);
    }

    public function testDownload()
    {
        $result = $this->getLocalFileImplementor()->download('1');
        $this->assertEquals(array(), $result);
    }

    public function testGetDefaultHumbnails()
    {
        $result = $this->getLocalFileImplementor()->getDefaultHumbnails('1');
        $this->assertEquals(array(), $result);
    }

    public function testGetFileWebPath()
    {
        $file = array('isPublic' => 1, 'hashId' => 'testHash');
        $file1 = array('isPublic' => 0, 'hashId' => 'testHash');
        $biz = $this->getBiz();
        $class = new LocalFileImplementorImpl($biz);
        $result = ReflectionUtils::invokeMethod($class, 'getFileWebPath', array($file));
        $this->assertEquals($biz['topxia.upload.public_url_path'].DIRECTORY_SEPARATOR.$file['hashId'], $result);
        $result1 = ReflectionUtils::invokeMethod($class, 'getFileWebPath', array($file1));
        $this->assertEquals('', $result1);
    }

    public function testGetThumbnail()
    {
        $result = $this->getLocalFileImplementor()->getThumbnail('1', array());
        $this->assertEquals(array(), $result);
    }

    public function testGetStatistics()
    {
        $result = $this->getLocalFileImplementor()->getStatistics(array());
        $this->assertEquals(array(), $result);
    }

    public function testPlayer()
    {
        $result = $this->getLocalFileImplementor()->player(1, false);
        $this->assertEquals(array(), $result);
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return LocalFileImplementorImpl
     */
    protected function getLocalFileImplementor()
    {
        return $this->getBiz()->service('File:LocalFileImplementor');
    }
}
