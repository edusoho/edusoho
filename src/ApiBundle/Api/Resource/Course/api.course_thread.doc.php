<?php

/**
 * @OA\Get(
 *     path="/api/courses/{courseId}/threads/{id}",
 *     tags={"course"},
 *     summary="获取单条问答/话题",
 *     @OA\Response(
 *         response=200,
 *         description="获取课程话题",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/course.thread")
 *         )
 *
 *     ),
 * )
 */

/**
 * @OA\Schema(
 *     schema="course.thread",
 *     title="course.thread",
 *     description="课程话题、问答",
 *     @OA\Property(property="id",title="ID",description="ID",type="number"),
 *     @OA\Property(property="courseId",title="courseId",description="课程ID",type="number"),
 *     @OA\Property(property="taskId",title="taskId",description="任务ID",type="number"),
 *
 * )
 */
