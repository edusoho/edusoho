# edusoho-h5 (h5微网校项目)

> 相关文档

- [接口地址](http://kb.codeages.net/edusoho/api/api-h5.html)
- [文档播放器地址](http://coding.codeages.net/qiqiuyun/api-doc/blob/master/v2/resource-play.md)
- [视频播放器文档](/doc/player.md)
- [需求文档](https://pro.modao.cc/app/43be7ceee9ba1239e1366453d273907de9ac2043#screen=sFAABE922B31526366021396)

## Build Setup

``` bash
# 安装依赖 (需要锁定版本安装依赖，不然 vant新版本引入方式有更改，会导致报错)
yarn

# 开发阶段
npm run dev:h5
npm run dev:admin

# build 打包
npm run build
npm run build:h5
npm run build:admin

# analyze 分析项目依赖
npm run analyze:h5
npm run analyze:admin

```

## 发布到测试站

1、安装 composer 中的依赖

```
composer require deployer/deployer --dev
```


2、找后端人员给予 deployerkey 文件（允许 ssh 到服务器的验证文件）
  放到~/.ssh/deployerkey目录下
  设置权限 600

```
sudo chmod 600 ~/.ssh/deployerkey
```

3、打包发布代码到 try 服务器（测试站地址: http://lvliujie.st.edusoho.cn, http://zhangfeng.st.edusoho.cn）

```
php vendor/bin/dep deploy dev
```

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

