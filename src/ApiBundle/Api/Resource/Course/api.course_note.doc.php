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
 *             @OA\Schema(ref="@/components/schemas/course.note")
 *         )
 *     ),
 * )
 */

/**
 * @OA\Schema(
 *      schema="course.note",
 *      title="course.note",
 *      description="课程笔记",
 *      @OA\Property(ref="#/components/schemas/user.simple")
 * )
 */