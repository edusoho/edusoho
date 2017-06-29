<?php

namespace Biz\Content\Service;

use Symfony\Component\HttpFoundation\File\File;

interface FileService
{
    public function getFileObject($fileId);

    /**
     * 获取最新的文件.
     *
     * @param string $group 文件组的编号，编号不为空则取该文件组下的文件
     * @param int    $start 取文件的开始记录数
     * @param int    $limit 取文件的记录条数
     *
     * @return array 文件列表
     */
    public function getFiles($group, $start, $limit);

    /**
     * 获取文件数量.
     *
     * @param string $group 文件组的编号，编号不为空则取该文件组下的文件数
     *
     * @return int 文件数量
     */
    public function getFileCount($group = null);

    /**
     * 获取目标对象所绑定的所有文件.
     *
     * @param string $target 目标对象名
     *
     * @return array 文件列表
     */
    public function getTargetFiles($target);

    /**
     * 上传文件.
     *
     * @param string $group  文件存储组
     * @param File   $file   上传的文件
     * @param string $target 文件绑定的目标对象
     *
     * @return array 文件记录，包含File对象
     */
    public function uploadFile($group, File $file, $target = null);

    /**
     * 删除文件.
     *
     * @param int $id 文件ID
     *
     * @return interger 删除的文件数
     */
    public function deleteFile($id);

    public function deleteFileByUri($uri);

    /**
     * 绑定文件到目标对象
     *
     * @param int    $id     文件ID
     * @param string $target 目标对象名
     *
     * @return interger 绑定的文件数
     */
    public function bindFile($id, $target);

    /**
     * 批量绑定文件到目标对象
     *
     * @param array  $ids    一批文件ID
     * @param string $target 目标对象名
     *
     * @return interger 绑定的文件数
     */
    public function bindFiles(array $ids, $target);

    /**
     * 解除文件与目标对象的绑定.
     *
     * 当此文件与任何对象未绑定时，则删除该文件
     *
     * @param int    $id     文件ID
     * @param string $target 目标对象名
     *
     * @return interger 解除绑定的文件数
     */
    public function unbindFile($id, $target);

    /**
     * 批量解除文件与目标对象的绑定.
     *
     * @param array  $ids    一批文件ID
     * @param string $target 目标对象名
     *
     * @return interger 解除绑定的文件数
     */
    public function unbindFiles(array $ids, $target);

    /**
     * 解除目标对象与所有文件的绑定.
     *
     * @param string $target 目标对象名
     *
     * @return interger 解除绑定的文件数
     */
    public function unbindTargetFiles($target);

    /**
     * 解析文件的URI.
     *
     * @param string $uri 文件URI
     *
     * @return array 返回数组包含access访问, path, directory, name
     */
    public function parseFileUri($uri);

    /**
     * 获得文件组.
     *
     * @param int $id 文件组ID
     *
     * @return array 文件组
     */
    public function getFileGroup($id);

    /**
     * 根据文件组的CODE，获得文件组.
     *
     * @param int $id 文件组ID
     *
     * @return array 文件组
     */
    public function getFileGroupByCode($code);

    /**
     * 获得所有文件组.
     *
     * @return array 文件组列表
     */
    public function getAllFileGroups();

    public function addFileGroup($group);

    public function deleteFileGroup($id);

    /**
     * [thumbnailFile description].
     *
     * @return [type]
     */
    public function thumbnailFile(array $file, array $options);

    public function getFile($id);

    public function getFilesByIds($ids);

    public function getImgFileMetaInfo($fileId, $scaledWidth, $scaledHeight);
}
