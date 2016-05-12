<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;
use Mockery;

// TODO

class UploadFileServiceTest extends BaseTestCase
{
    public function testGetFile()
    {
       $fileId = 1;
       $name = 'File.UploadFileDao';
       $params = array(
         array(
             'functionName' => 'getFile',
             'runTimes' => 1,
             'withParams' => array(1),
             'returnValue' =>array(
               'id' => 1,
               'storage' => 'cloud',
               'filename'=> 'test',
               'createdUserId' => 1
             )
           )
        );
        $this->mock($name,$params);
        $name = 'File.CloudFileImplementor';
        $params = array(
           array(
             'functionName' => 'getFile',
             'runTimes' => 1,
             'withParams' => array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            ),
             'returnValue' =>array(
               'id' => 1,
               'storage' => 'cloud',
               'filename'=> 'test',
               'createdUserId' => 1
             )
           )
         );
       $this->mock($name,$params);

       $file = $this->getUploadFileService()->getFile($fileId);

       $this->assertEquals($file['id'],$fileId);
    }

    public function testGetFileFromLeaf()
    {
       $fileId = 1;
       $name = 'File.UploadFileDao';
       $params = array(
         array(
             'functionName' => 'getFile',
             'runTimes' => 1,
             'withParams' => array(1),
             'returnValue' =>array(
               'id' => 1,
               'storage' => 'cloud',
               'filename'=> 'test',
               'createdUserId' => 1,
               'globalId' => '535399bd5f19413c9339a5ab11c3a5d1'
             )
           )
        );
        $this->mock($name,$params);
        $name = 'File.CloudFileImplementor';
        $params = array(
           array(
             'functionName' => 'getFileFromLeaf',
             'runTimes' => 1,
             'withParams' => array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            ),
             'returnValue' =>array(
               'id' => 1,
               'storage' => 'cloud',
               'filename'=> 'test',
               'createdUserId' => 1
             )
           )
         );
       $this->mock($name,$params);

       $file = $this->getUploadFileService()->getFileFromLeaf($fileId);

       $this->assertEquals($file['id'],$fileId);
    }

    public function testGetFileByGlobalId()
    {
        $name = 'File.UploadFileDao';
        $params = array(
            array(
                'functionName' => 'getFileByGlobalId',
                'runTimes' => 1,
                'withParams' => array('65d474f089074fa0810d1f2f146fd218'),
                'returnValue' =>array(
                  'id'            => 1,
                  'globalId'      =>'65d474f089074fa0810d1f2f146fd218',
                  'storage'       => 'cloud',
                  'filename'      => 'test',
                  'createdUserId' => 1
                )
            ),  
        );
        $this->mock($name,$params);

        $name = 'File.CloudFileImplementor';
            $params = array(
                array(
                  'functionName' => 'getFile',
                  'runTimes' => 1,
                  'withParams' => array(),
                  'returnValue' =>array(
                        'id' => 1,
                        'globalId' =>'65d474f089074fa0810d1f2f146fd218',
                        'storage' => 'cloud',
                        'filename'=> 'test',
                        'createdUserId' => 1
                    )
                )
            );
        $this->mock($name,$params);

        $name = 'File.LocalFileImplementor';
        $params = array(
            array(
              'functionName' => 'getFile',
              'runTimes'    => 1,
              'withParams'  => array(),
              'returnValue' => array(
                'id'            => 2,
                'globalId'      => 0,
                'storage'       => 'local',
                'filename'      => 'localFileName',
                'createdUserId' => 1
              )
            )
        );
        $this->mock($name,$params);

        $name = 'File.CloudFileImplementor';
        $params = array(
            array(
             'functionName' => 'getFileFromLeaf',
             'runTimes' => 1,
             'withParams' => array(),
             'returnValue' =>array(
               'id' => 1,
               'globalId' =>'65d474f089074fa0810d1f2f146fd218',
               'storage' => 'cloud',
               'filename'=> 'test',
               'createdUserId' => 1
            )
          )
        );
        $this->mock($name,$params);

        $globalId = '65d474f089074fa0810d1f2f146fd218';
        $cloudFile = $this->getUploadFileService()->getFileByGlobalId($globalId);

        $this->assertEquals($globalId, $cloudFile['globalId']);
    }

    public function testGetFileByHashId()
    {
      $hashId = 'materiallib-1/20160418040438-d11n060aceo8g8ws';
      $name = 'File.UploadFileDao';
      $params = array(
        array(
          'functionName' => 'getFileByHashId',
          'runTimes' => 1,
          'withParams' => array('materiallib-1/20160418040438-d11n060aceo8g8ws'),
          'returnValue' => array(
            'id' => 1,
            'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'storage' => 'cloud',
            'filename'=> 'test',
            'createdUserId' => 1
          )
        )
      );
      $this->mock($name,$params);
      $name = 'File.CloudFileImplementor';
      $params = array(
        array(
          'functionName' => 'getFile',
          'runTimes' => 1,
          'withParams' =>array(array(
           'id' => 1,
           'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
           'storage' => 'cloud',
           'filename'=> 'test',
           'createdUserId' => 1
         )),
          'returnValue' => array(
            'id' => 1,
            'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'storage' => 'cloud',
            'filename'=> 'test',
            'createdUserId' => 1
          )
        )
      );
      $this->mock($name,$params);
      $file = $this->getUploadFileService()->getFileByHashId($hashId);
      $this->assertEquals($file['hashId'],$hashId);
    }

    public function testGetFileByConvertHash()
    {
      $hash = 'materiallib-1/20160418040438-d11n060aceo8g8ws';
      $name = 'File.UploadFileDao';
      $params = array(
        array(
          'functionName' => 'getFileByConvertHash',
          'runTimes' => 1,
          'withParams' => array('materiallib-1/20160418040438-d11n060aceo8g8ws'),
          'returnValue' => array(
            'id' => 1,
            'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
            'storage' => 'cloud',
            'filename'=> 'test',
            'createdUserId' => 1
          )
        )
      );
      $this->mock($name,$params);
      $file = $this->getUploadFileService()->getFileByConvertHash($hash);
      $this->assertEquals($file['convertHash'],$hash);
    }

    public function testFindFilesByIds()
    {
      $ids = array(1,2);
      $name = 'File.UploadFileDao';
      $params = array(
        array(
          'arguments' => true,
          'functionName' => 'findFilesByIds',
          'runTimes' => 1,
          'withParams' =>  array(array(1,2)),
          'returnValue' => array(
            array(
              'id' => 1,
              'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            ),
            array(
              'id' => 2,
              'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
          )
        )
      );
      $this->mock($name,$params);
      $files = $this->getUploadFileService()->findFilesByIds($ids);
      $this->assertEquals($files[0]['id'],1);
      $this->assertEquals($files[1]['id'],2);
    }

    public function testSearchFiles()
    {
      $conditions = array(
        'source' => 'shared',
        'currentUserId' => 1
      );
      $withConditions = array(
        'source' => 'shared',

        'currentUserIds' =>array(
          1,
          2,
        ),
        'currentUserId' => 1
      );
      
      $orderBy = array('createdTime','DESC');
      $start = 0;
      $limit = 20;
      $name = 'File.UploadFileDao';
      $params = array(
        array(
          'functionName' => 'searchFiles',
          'runTimes' => 1,
          'withParams' =>  array($withConditions,$orderBy,$start,$limit),
          'returnValue' => array(
            array(
              'id' => 1,
              'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            ),
            array(
              'id' => 2,
              'hashId' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'convertHash' => 'materiallib-1/20160418040438-d11n060aceo8g8ws',
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
          )
        )
      );
      $this->mock($name,$params);
      $name = 'File.UploadFileShareDao';
      $params = array(
        array(
          'functionName' => 'findShareHistoryByUserId',
          'runTimes' => 1,
          'withParams' =>  array(1),
          'returnValue' => array(
            array(
              'id' => 1,
              'sourceUserId' => 1,
              'targetUserId' => 2,
              'isActive' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            ),
            array(
              'id' => 2,
              'sourceUserId' => 2,
              'targetUserId' => 3,
              'isActive' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            )
          )
        )
      );
      $this->mock($name,$params);
      $files = $this->getUploadFileService()->searchFiles($conditions, $orderBy, $start, $limit);
      $this->assertEquals($files[0]['id'],1);
      $this->assertEquals($files[1]['id'],2);
    }

    public function testSearchFileCount()
    {
      $conditions = array(
        'source' => 'shared',
        'currentUserId' => 1
      );
      $name = 'File.UploadFileShareDao';
      $params = array(
        array(
          'functionName' => 'findShareHistoryByUserId',
          'runTimes' => 1,
          'withParams' =>  array(1),
          'returnValue' => array(
            array(
              'id' => 1,
              'sourceUserId' => 2,
              'targetUserId' => 1,
              'isActive' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            ),
            array(
              'id' => 2,
              'sourceUserId' => 3,
              'targetUserId' => 1,
              'isActive' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            )
          )
        )
      );

      $this->mock($name,$params);
      $name = 'File.UploadFileCollectDao';
      $params = array(
        array(
          'functionName' => 'findCollectionsByUserId',
          'runTimes' => 1,
          'withParams' =>  array(1),
          'returnValue' => array(
            array(
              'id' => 1,
              'fileId' => 1,
              'userId' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            ),
            array(
              'id' => 2,
              'fileId' => 2,
              'userId' => 1,
              'createdTime'=> 1461037751,
              'updatedTime' => 1461037751
            )
          )
        )
      );
      $this->mock($name,$params);
      $name = 'File.UploadFileDao';
      $params = array(
        array(
          'functionName' => 'searchFileCount',
          'runTimes' => 1,
          'withParams' =>  array(1),
          'returnValue' => 2
        )
      );
      $this->mock($name,$params);
      $count = $this->getUploadFileService()->searchFileCount($conditions);
      $this->assertEquals($count,2);
    }

    public function testAddFile()
    {
      $name = 'File.UploadFileDao';
      $params = array(
        array(
            'functionName' => 'addFile',
            'runTimes' => 1,
            'withParams' => array(1),
            'returnValue' =>array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
          )
       );
       $this->mock($name,$params);
       $name = 'File.LocalFileImplementor';
       $params = array(
          array(
            'functionName' => 'addFile',
            'runTimes' => 1,
            'withParams' => array(
             'id' => 1,
             'storage' => 'cloud',
             'filename'=> 'test',
             'createdUserId' => 1
           ),
            'returnValue' =>array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
          )
        );
      $this->mock($name,$params);
      $file = $this->getUploadFileService()->addFile('materiallib',1);
      $this->assertEquals($file['id'],1);
    }

    public function testRenameFile()
    {
      $id = 1;
      $newFileName = 'test2';
      $name = 'File.UploadFileDao';
      $params = array(
         array(
           'functionName' => 'updateFile',
           'runTimes' => 1,
           'withParams' => array(
            'id' => 1,
            'filename'=> array(
              'filename'=>'test2'
            )
          ),
           'returnValue' =>array(
             'id' => 1,
             'storage' => 'cloud',
             'filename'=> 'test2',
             'createdUserId' => 1
           )
         ),
         array(
             'functionName' => 'getFile',
             'runTimes' => 1,
             'withParams' => array(1),
             'returnValue' =>array(
               'id' => 1,
               'convertParams' => null,
               'metas' => null,
               'metas2' => null,
               'storage' => 'cloud',
               'filename'=> 'test2',
               'createdUserId' => 1
             )
           )
       );
       $this->mock($name,$params);
       $file = $this->getUploadFileService()->renameFile($id,$newFileName);
       $this->assertEquals($file['filename'],'test2');
    }

    public function testDeleteFile()
    {
      $id = 1;
      $name = 'File.UploadFileDao';
      $params = array(
         array(
           'functionName' => 'deleteFile',
           'runTimes' => 1,
           'withParams' => array(
              'id' => 1
            ),
           'returnValue' => true
         ),
         array(
           'functionName' => 'getFile',
           'runTimes' => 1,
           'withParams' => array(
            'id' => 1
            ),
            'returnValue' => array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
         )
       );
       $this->mock($name,$params);
       $name = 'File.CloudFileImplementor';
       $params = array(
          array(
            'functionName' => 'deleteFile',
            'runTimes' => 1,
            'withParams' => array(
             'id' => 1,
             'storage' => 'cloud',
             'filename'=> 'test',
             'createdUserId' => 1
           ),
            'returnValue' => true
          ),
          array(
            'functionName' => 'getFile',
            'runTimes' => 1,
            'withParams' => array(
             'id' => 1,
             'storage' => 'cloud',
             'filename'=> 'test',
             'createdUserId' => 1
           ),
            'returnValue' => array(
              'id' => 1,
              'storage' => 'cloud',
              'filename'=> 'test',
              'createdUserId' => 1
            )
          )
        );
      $this->mock($name,$params);
      $result = $this->getUploadFileService()->deleteFile($id);
      $this->assertEquals($result,true);
    }

  	protected function getUploadFileService()
  	{
  		return $this->getServiceKernel()->createService('File.UploadFileService');
  	}

}
