<?php

/**
 * @OA\Tag(
 *     name="setting",
 *     description="系统设置接口集合"
 * )
 *
 * @OA\Tag(
 *     name="course",
 *     description="课程接口集合"
 * )
 *
 * @OA\Tag(
 *     name="user",
 *     description="用户接口集合"
 * )
 */

/**
 * @OA\Schema(
 *     description="公共分页paging",
 *     type="object",
 *     schema="common.paging",
 *     title="common.paging",
 *     @OA\Property(
 *         property="paging",
 *         @OA\Property(property="total", type="integer",default=0),
 *         @OA\Property(property="offset", type="integer", default=0),
 *         @OA\Property(property="limit", type="integer", default=10)
 *     ),
 * )
 */
