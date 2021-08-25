<?php

/**
 * @OA\Post(
 *     path="/api/courses/{courseId}/notes/{noteId}/like",
 *     tags={"course"},
 *     summary="课程笔记点赞接口",
 *     @OA\Response(
 *         response=200,
 *         description="笔记点赞回执",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(
 *                 @OA\Property(property="success", title="success", type="boolean"),
 *             ),
 *         ),
 *     ),
 * )
 *
 * @OA\Delete(
 *     path="/api/courses/{courseId}/notes/{noteId}/like",
 *     tags={"course"},
 *     summary="课程笔记取消点赞接口",
 *     @OA\Response(
 *         response=200,
 *         description="笔记取消点赞回执",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(
 *                 @OA\Property(property="success", title="success", type="boolean"),
 *             ),
 *         ),
 *     ),
 * )
 */
