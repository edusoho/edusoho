<?php

/**
 * @OA\Get(
 *     path="/api/courses/{courseId}/live_replay/{liveId}",
 *     tags={"course"},
 *     description="VERSION >= 21.3.6",
 *     summary="课程自研直播任务回放下载接口",
 *     @OA\Parameter(
 *         name="courseId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Parameter(
 *         name="liveId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="回放下载接口",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(
 *                 @OA\Property(property="url",description="下载地址",type="string"),
 *                 @OA\Property(property="token",description="token",type="string"),
 *                 @OA\Property(property="roomId",description="roomId",type="number"),
 *                 @OA\Property(property="type",description="直播供应商类型",type="string",enum={"selfLive"})
 *             )
 *         )
 *     )
 * )
 */
