<?php

/**
 * @OA\Schema(
 *     schema="course.task.public",
 *     title="course.task.public",
 *     description="课程任务",
 *     @OA\Property(property="id",title="id",description="ID",type="number"),
 *     @OA\Property(property="seq",title="seq",description="任务排序",type="number"),
 *     @OA\Property(property="categoryId",title="categoryId",description="course_chapter.id",type="number"),
 *     @OA\Property(property="title",title="title",description="标题",type="string"),
 *     @OA\Property(property="isOptional",title="isOptional",description="是否选修",type="number"),
 *     @OA\Property(property="startTime",title="startTime",description="任务开始时间",type="number"),
 *     @OA\Property(property="endTime",title="endTime",description="任务截止时间",type="number"),
 *     @OA\Property(property="mode",title="mode",description="任务类型",type="string"),
 *     @OA\Property(property="status",title="status",description="任务状态",enum={"create","published","unpublished"}),
 *     @OA\Property(property="number",title="number",description="课时序号",type="number"),
 *     @OA\Property(property="type",title="type",description="任务类型",type="string"),
 *     @OA\property(property="mediaSource",title="mediaSource",description="媒体资源来源默认self",type="string"),
 *     @OA\Property(property="length",title="length",description="长度可以是考试时长音视频时长等",type="number")
 * )
 */
