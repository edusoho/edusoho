<?php

/**
 * @OA\Get(
 *     path="/api/settings/signSecurity",
 *     tags={"setting"},
 *     summary="获取接口加密设置",
 *     @OA\Response(
 *          response="200",
 *          description="加密设置",
 *          @OA\MediaType(
 *              mediaType="application/vnd.edusoho.v2+json",
 *              @OA\Schema(ref="#/components/schemas/setting.signSecurity"),
 *          ),
 *     )
 * )
 * @OA\Get(
 *     path="/api/settings/locale",
 *     tags={"setting"},
 *     summary="获取系统语言设置",
 *     @OA\Response(
 *         response=200,
 *         description="语言设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.locale"),
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/api/settings/ugc",
 *     summary="获取UGC设置",
 *     tags={"setting"},
 *     @OA\Response(
 *         response=200,
 *         description="全部UGC设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.ugc"),
 *         ),
 *     )
 * )
 * @OA\Get(
 *     path="/api/settings/ugc_review",
 *     summary="获取评价UGC设置",
 *     tags={"setting"},
 *     @OA\Response(
 *         response=200,
 *         description="评价UGC设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.ugc_review"),
 *         ),
 *     )
 * )
 * @OA\Get(
 *     path="/api/settings/ugc_note",
 *     summary="获取笔记UGC设置",
 *     tags={"setting"},
 *     @OA\Response(
 *         response=200,
 *         description="笔记UGC设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.ugc_note"),
 *         ),
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/settings/ugc_thread",
 *     summary="获取话题问答UGC设置",
 *     tags={"setting"},
 *     @OA\Response(
 *         response=200,
 *         description="话题问答UGC设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.ugc_thread"),
 *         ),
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/settings/ugc_private_message",
 *     summary="获取私信UGC设置",
 *     tags={"setting"},
 *     @OA\Response(
 *         response=200,
 *         description="私信UGC设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.ugc_private_message"),
 *         ),
 *     )
 * )
 * @OA\Get(
 *     path="/api/settings/task_learning_config",
 *     tags={"setting"},
 *     summary="任务学习设置",
 *     @OA\Response(
 *         response=200,
 *         description="任务学习设置",
 *         @OA\MediaType(
 *             mediaType="application/vnd.edusoho.v2+json",
 *             @OA\Schema(ref="#/components/schemas/setting.task_learning_config")
 *         )
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="setting.signSecurity",
 *     title="setting.signSecurity",
 *     description="接口安全加密设置信息",
 *     @OA\Property(
 *          property="level",
 *          title="level",
 *          description="安全校验等级：close（不校验）、optional（佛系校验）、open（强制校验）",
 *          type="string",
 *          enum={"close", "optional","open"},
 *          default="close",
 *     ),
 *     @OA\Property(
 *          property="clients",
 *          title="clients",
 *          description="需要校验的设备列表：包含ios、android、miniprogram、other",
 *          type="array",
 *          default=null,
 *          @OA\Items(
 *               type="string",
 *               enum = {"ios", "android", "miniprogram","other"},
 *          ),
 *     )
 * )
 * @OA\Schema(
 *     schema="setting.locale",
 *     title="setting.locale",
 *     description="语言信息",
 *     @OA\Property(
 *          property="locale",
 *          title="locale",
 *          description="语言：en,zh_CN",
 *          type="string",
 *          default="zh_CN",
 *          enum = {"en", "zh_CN"}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="setting.ugc_review",
 *     title="setting.ugc_review",
 *     description="评价UGC设定",
 *     @OA\Property(property="enable", title="enable", description="评价总开关", type="integer", default=1),
 *     @OA\Property(property="course_enable", title="course_enable", description="课程评价开关", type="integer", default=1),
 *     @OA\Property(property="classroom_enable", title="classroom_enable", description="班级评价开关", type="integer", default=1),
 *     @OA\Property(property="question_bank_enable", title="question_bank_enable", description="题库评价开关", type="integer", default=1),
 *     @OA\Property(property="open_course_enable", title="open_course_enable", description="公开课评价开关", type="integer", default=1),
 *     @OA\Property(property="article_enable", title="article_enable", description="资讯评论开关", type="integer", default=1),
 * )
 *
 * @OA\Schema(
 *     schema="setting.ugc_note",
 *     title="setting.ugc_note",
 *     description="笔记UGC设定",
 *     @OA\Property(property="enable", title="enable", description="笔记总开关", type="integer", default=1),
 *     @OA\Property(property="classroom_enable", title="classroom_enable", description="班级笔记开关", default=1),
 *     @OA\Property(property="course_enable", title="course_enable", description="课程笔记开关", default=1),
 * )
 *
 * @OA\Schema(
 *     schema="setting.ugc_thread",
 *     title="setting.ugc_thread",
 *     description="话题讨论UGC设定",
 *     @OA\Property(property="enable", title="enable", description="话题讨论总开关", type="integer", default=1),
 *     @OA\Property(property="course_question_enable", title="course_question_enable", description="课程提问开关", type="integer", default=1),
 *     @OA\Property(property="course_thread_enable", title="course_thread_enable", description="课程话题开关", type="integer", default=1),
 *     @OA\Property(property="classroom_question_enable", title="classroom_question_enable", description="班级提问开关", type="integer", default=1),
 *     @OA\Property(property="classroom_thread_enable", title="classroom_thread_enable", description="班级话题开关", type="integer", default=1),
 *     @OA\Property(property="group_thread_enable", title="group_thread_enable", description="小组话题开关", type="integer", default=1),
 * )
 *
 * @OA\Schema(
 *     schema="setting.ugc_private_message",
 *     title="setting.ugc_private_message",
 *     description="私信UGC设定",
 *     @OA\Property(property="enable", title="enable", description="私信总开关", type="integer", default=1),
 *     @OA\Property(property="student_to_student", title="student_to_student", description="学员之间发送私信", type="integer", default=1),
 *     @OA\Property(property="student_to_teacher", title="student_to_teacher", description="学员向老师发送私信", type="integer", default=1),
 *     @OA\Property(property="teacher_to_student", title="teacher_to_student", description="教师向学员发送私信", type="integer", default=1),
 *
 * )
 *
 * @OA\Schema(
 *     schema="setting.ugc",
 *     title="setting.ugc",
 *     description="全局UGC设定",
 *     @OA\Property(property="ugc_review",ref="#/components/schemas/setting.ugc_review"),
 *     @OA\Property(property="ugc_note",ref="#/components/schemas/setting.ugc_note"),
 *     @OA\Property(property="ugc_thread",ref="#/components/schemas/setting.ugc_thread"),
 *     @OA\Property(property="ugc_private_message",ref="#/components/schemas/setting.ugc_private_message"),
 * )
 *
 * @OA\Schema(
 *     schema="setting.task_learning_config",
 *     title="setting.task_learning_config",
 *     description="课程任务学习配置",
 *     @OA\Property(property="non_focus_learning_video_play_rule",description="非专注学习规则,auto_pause:自动暂停、no_action:无操作",type="string",default="no_action",enum={"auto_pause","no_action"}),
 *     @OA\Property(
 *         property="multiple_learn",
 *         description="多开学习",
 *         type="object",
 *         @OA\Property(property="multiple_learn_enable",description="是否多开学习",type="string",default="off",enum={"on","off"}),
 *         @OA\Property(property="multiple_learn_kick_mode",description="多开学习模式，kick_previous：踢掉上一个播放、reject_current：拒绝本次播放",type="string",default="kick_previous",enum={"kick_previous","reject_current"}),
 *     ),
 * )
 */
