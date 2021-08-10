<?php

/**
 * @OA\Schema(
 *     schema="user.simple",
 *     title="user.simple",
 *     description="Simple用户对象",
 *     @OA\Property(property="id",description="用户ID", type="integer"),
 *     @OA\Property(property="nickname",description="nickname", type="string"),
 *     @OA\Property(property="title",description="头衔", type="string"),
 *     @OA\Property(property="uuid",description="用户UUID", type="string"),
 *     @OA\Property(property="destroyed",description="是都已注销，0|1", type="integer"),
 *     @OA\Property(
 *         property="avatar",
 *         description="是都已注销，0|1",
 *         type="integer",
 *         @OA\Property(property="small",description="小图", type="string"),
 *         @OA\Property(property="middle",description="中图", type="string"),
 *         @OA\Property(property="large",description="大图", type="string"),
 *     ),
 * )
 */