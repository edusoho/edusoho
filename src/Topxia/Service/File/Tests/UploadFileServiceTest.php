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

    public function testGetFullFile()
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
             'functionName' => 'getFullFile',
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

       $file = $this->getUploadFileService()->getFullFile($fileId);

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
             'functionName' => 'getFullFile',
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
          'functionName' => 'findSharesByTargetUserIdAndIsActive',
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
        $name   = 'File.UploadFileShareDao';
        $params = array(
            array(
                'functionName' => 'findSharesByTargetUserIdAndIsActive',
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

    public function testFindMySharingContacts()
    {
        $user1 = $this->createUser('111111');
        $user2 = $this->createUser('222222');
        $user3 = $this->createUser('333333');

        $fileShare1 = $this->getUploadFileService()->addShare($user1['id'], $user2['id']);
        $fileShare2 = $this->getUploadFileService()->addShare($user1['id'], $user3['id']);
        $fileShare3 = $this->getUploadFileService()->addShare($user2['id'], $user3['id']);

        $sourceUsers = $this->getUploadFileService()->findMySharingContacts($user3['id']);

        $this->assertArrayHasKey($user1['id'], $sourceUsers);
        $this->assertArrayHasKey($user2['id'], $sourceUsers);
    }

    public function testShareFiles()
    {
        $this->getUploadFileService()->shareFiles(1, array(2,3,4));
        $userShare = $this->getUploadFileService()->findShareHistoryByUserId(1,3);

        $this->assertEquals(1, $userShare['sourceUserId']);
        $this->assertEquals(3, $userShare['targetUserId']);
    }

    public function testAddShare()
    {
        $sourceUserId = 1;
        $targetUserId = 2;

        $fileShare = $this->getUploadFileService()->addShare($sourceUserId, $targetUserId);

        $this->assertEquals($sourceUserId, $fileShare['sourceUserId']);
        $this->assertEquals($targetUserId, $fileShare['targetUserId']);
    }

    public function testUpdateShare()
    {
        $sourceUserId = 1;
        $targetUserId = 2;
        $fileShare    = $this->getUploadFileService()->addShare($sourceUserId, $targetUserId);

        $updateFileShare = $this->getUploadFileService()->updateShare($fileShare['id']);
        $this->assertEquals($sourceUserId, $updateFileShare['sourceUserId']);
        $this->assertEquals($targetUserId, $updateFileShare['targetUserId']);
        $this->assertEquals(1, $updateFileShare['isActive']);
    }

    public function testFindShareHistoryByUserId()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);

        $userSharefile = $this->getUploadFileService()->findShareHistoryByUserId(1, 2);

        $this->assertEquals(1, $userSharefile['sourceUserId']);
        $this->assertEquals(2, $userSharefile['targetUserId']);
    }

    public function testFindShareHistory()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareFiles = $this->getUploadFileService()->findShareHistory(1);

        $this->assertEquals(2, count($shareFiles));
    }

    public function testFindActiveShareHistory()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $fileShares = $this->getUploadFileService()->findActiveShareHistory(1);

        $this->assertEquals(2, count($fileShares));
    }

    public function testCancelShareFile()
    {
        $fileShare = $this->getUploadFileService()->addShare(1, 2);
        $fileShare = $this->getUploadFileService()->cancelShareFile(1, 2);

        $this->assertEquals(0, $fileShare['isActive']);
    }

    public function testSearchShareHistoryCount()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareCount = $this->getUploadFileService()->searchShareHistoryCount(array('sourceUserId'=>1,'isActive'=>1));

        $this->assertEquals(2, $shareCount);
    }

    public function testSearchShareHistories()
    {
        $fileShare1 = $this->getUploadFileService()->addShare(1, 2);
        $fileShare2 = $this->getUploadFileService()->addShare(1, 3);
        $fileShare3 = $this->getUploadFileService()->addShare(2, 3);

        $shareFiles = $this->getUploadFileService()->searchShareHistories(array(
                'sourceUserId' => 1
            ), 
            array('createdTime','DESC'),
            0, 10
        );

        $this->assertEquals(2, count($shareFiles));
    }

    public function testWaveUploadFile()
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
                  'createdUserId' => 1,
                  'usedCount' => 0
                )
            ),
            array(
                'functionName' => 'waveUploadFile',
                'runTimes' => 1,
                'withParams' => array(1),
                'returnValue' =>array(
                  'id' => 1,
                  'storage' => 'cloud',
                  'filename'=> 'test',
                  'createdUserId' => 1,
                  'usedCount' => 1
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

        $updateFile = $this->getUploadFileService()->waveUploadFile($file['id'], 'usedCount', +1);

        $this->assertEquals($file['usedCount']+1, $updateFile['usedCount']);
    }

    public function testSyncToLocalFromCloud()
    {
        $cloudFile = array(
            'globalId' => '507868be3524496eb80c8df7c4ceeeda',
            'hashId' => 'courselesson-21/20161123082458-2r6306vs6l2c0884',
            'filename' => '06.如何修改中差评.mp4',
            'ext' => 'mp4',
            'fileSize' => '10507724',
            'etag' => 'courselesson-21/20161123082458-2r6306vs6l2c0884',
            'length' => '494',
            'description' => '',
            'status' => 'ok',
            'convertHash' => '',
            'convertStatus' => 'success',
            'targetId' => 1,
            'targetType' => 'replay',
            'metas' => '',
            'metas2' => '',
            'type' => 'video',
            'storage' => 'cloud',
            'createdUserId' => 1,
            'updatedUserId' => 1
        );

        $result = $this->getUploadFileService()->syncToLocalFromCloud($cloudFile);

        $this->assertTrue($result);
    }

    protected function createUser($user)
    {
        $userInfo             = array();
        $userInfo['email']    = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp']  = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

  	protected function getUploadFileService()
  	{
  		return $this->getServiceKernel()->createService('File.UploadFileService');
  	}

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}
