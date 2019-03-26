## 班级、计划加入逻辑

加入计划判断链

1.计划不存在：course.not_found
2.草稿状态计划：course.unpublished
3.计划已关闭：course.closed
4.计划不可购买（加入）：course.not_buyable
4.1.如果仅vip加入且用户会员等级不够：course.only_vip_join_way
5.计划有效期已过期：course.expired
6.加入截止日期到期：course.buy_expired
7.学员达到上限：course.reach_max_student_num


8.用户未登录：user.not_login
9.用户被锁定：user.locked
10.计划学员已存在（已加入）：member.member_exist


加入班级判断链

1.班级不存在：classroom.not_found
2.草稿状态班级：classroom.unpublished
3.班级已关闭：classroom.closed
4.班级不可购买（加入）：classroom.not_buyable
4.1.如果仅vip加入且用户会员等级不够：course.only_vip_join_way（这里有问题，应该是classroom.only_vip_join_way
）
5.班级有效期已过期：classroom.expired


6.用户未登录：user.not_login
7.用户被锁定：user.locked
8.课程学员已存在（已加入，不包括旁听生）：member.member_exist

## 计划、班级学习逻辑

计划学习判断链

1.计划不存在：course.not_found
2.草稿状态计划：course.unpublished
3.计划有效期已过期：course.expired
4.学习有效期开始时间未到：course.not_arrive

5-8前提：用户不是管理员
5.用户未登录：user.not_login
6.用户被锁定（封禁）：user.locked
7.用户未加入：member.not_found
8.学习有效期已过：member.expired

9-14前提：计划是vip免费加入／用户不是计划教师／用户是vip免费学加入的
9.网站关闭vip：vip.vip_closed
10.用户未登录：vip.not_login
11.非vip：vip.not_member
12.vip已过期：vip.member_expired
13.用户当前的vip会员等级或计划允许免费加入的vip等级不存在：vip.level_not_exist
14.当前用户会员等级比计划允许免费加入的等级低：vip.level_low


班级学习判断链

1.班级不存在：classroom.not_found
2.草稿状态班级：classroom.unpublished
3.班级有效期已过期：classroom.expired

与计划不同，这里没加管理员判断
4.用户未登录：user.not_login
5.用户被锁定（封禁）：user.locked
6.用户未加入：member.not_found
7.用户是旁听生：member.auditor
8.学习有效期已过：member.expired

9-14前提：班级是vip免费加入／用户不是助教、老师、班主任／用户是vip免费学加入的
9.网站关闭vip：vip.vip_closed
10.用户未登录：vip.not_login
11.非vip：vip.not_member
12.vip已过期：vip.member_expired
13.用户当前的vip会员等级或计划允许免费加入的vip等级不存在：vip.level_not_exist
14.当前用户会员等级比计划允许免费加入的等级低：vip.level_low
