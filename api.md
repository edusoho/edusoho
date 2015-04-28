## Api请求说明:
- 请求支持GET,POST

#### 请求格式 : http://hostname/mapi_v2/service/method
#### Example : http://trymob.edusoho.cn/mapi_v2/Category/getAllCategories
#### 备注:
* hostname  域名
* service   服务接口名
* method    接口方法
* 没有标注的方法，参数都是可选

## Api服务列表

* [Category](#Category)
* Course
* Lesson
* User
* Order
* School
* Testpaper

<a id="Category" />
### Category 接口方法列表

* getCategories *获取指定分类*

```
  参数          类型            描述
  category      String          父级分类名称
  
  返回
  json
```
    
* getAllCategories  *获取所有分类*

```
  返回
  json
```
* getTags   *获取所有标签*

```
  最大返回100条
  返回
  json
```

<a id="Course" />
### Course 接口方法列表

* getCourseNotices  *获取课程公告*

```
  参数          类型            描述
  courseId      int             课程id(必选)
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* getLessonNote     *获取课时笔记*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  lessonId      int             课时id(必选)
  limit         int             limit
  
  返回
  json
```

* getCourseMember       *获取课程会员信息*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  
  返回
  json
```

* postThread    *发表评论/问答*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  threadId      int             评论/问答id(必选)
  content       String          评论内容(必选)
  
  返回
  json
```

* commitCourse     *评价课程*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  rating        int             评论星数(必选)
  postId        int             回复id(必选)
  content       String          评论内容(必选)
  
  返回
  json
```

* getCourseThreads      *获取课程问答/讨论*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  lessonId      int             课时id(必选)
  type          String          默认question（question,thread)
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* getCourseNotes    *获取课程笔记*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* getThreadPost     *获取讨论/问答回复*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  threadId      int             讨论/问答id
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* getThread     *获取指定问答/讨论*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id(必选)
  threadId      int             讨论/问答id
  
  返回
  json
```

* getFavoriteCoruse     *获取收藏课程*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* getReviews        *获取课程评论*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id
  start         int             起始索引
  limit         int             limit
  
  返回
  json
```

* favoriteCourse        *收藏课程*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id
  
  返回
  bool
```

* unFavoriteCourse        *取消收藏课程*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id
  
  返回
  bool
```

* getTeacherCourses        *获取教师课程*

```
  参数          类型            描述
  userId        int             教师用户id
  
  返回
  json
```

* vipLearn        *vip学习课程*

```
  请求头        类型            描述
  token         String          用户登录token，验证请求权限
  
  参数          类型            描述
  courseId      int             课程id
  
  返回
  bool
```