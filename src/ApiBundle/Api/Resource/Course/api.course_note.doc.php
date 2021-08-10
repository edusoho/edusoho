<?php

/**
 * @OA\Get(
 *     path="/api/course/{courseId}/notes/{noteId}",
 *     tags={"course"},
 *     summary="课程单个笔记获取接口",
 *     @OA\Response(
 *         response=200,
 *         description="单条笔记",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/course.note")
 *         )
 *     ),
 * )
 */

/**
 * @OA\Schema(
 *      schema="course.note",
 *      title="course.note",
 *      description="课程笔记",
 *      @OA\Property(property="id",title="id",description="ID",type="integer"),
 *      @OA\Property(property="userId",title="userId",description="userId",type="integer"),
 *      @OA\Property(property="taskId",title="taskId",description="taskId",type="integer"),
 *      @OA\Property(property="content",title="content",description="content",type="string"),
 *      @OA\Property(property="length",title="length",description="length",type="integer"),
 *      @OA\Property(property="likeNum",title="likeNum",description="likeNum",type="integer"),
 *      @OA\Property(property="createdTime",title="createdTime",description="创建时间",type="datetime"),
 *      @OA\Property(property="updatedTime",title="updatedTime",description="更新时间",type="datetime"),
 *      @OA\Property(property="user",title="id",ref="#/components/schemas/user.simple")
 * )
 */