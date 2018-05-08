# 获取教学计划信息
# 参数解释 
#   api verison: 标记版本，api分为3个版本，目前只支持用第3个版本
#   api url: api 路由
#   api method: 支持 PUT, GET, POST, PATCH 4种，目前只支持api 3.0
#   api login: 有此属性的api，需要登录才能访问，即要带token
#   api-url-editable: 值为true时，通过在上面的api-url 输入框内输入url, 可修改路由
#   api authorized: 有此属性的api，一般会加上标签 @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN") 

api-version: 3
api-url: /api/courses
api-method: GET
api-authorized: false
api-login: false

{
    "offset": "0",  //默认为0，取第 (offset + 1) ~ (offset + limit) 条数据
    "limit": "20",
    // 可供查询的查询条件见 下面
}

'id = :id',
'courseSetId = :courseSetId',
'courseSetId IN (:courseSetIds)',
'updatedTime >= :updatedTime_GE',
'status = :status',
'type = :type',
'price = :price',
'price > :price_GT',
'price >= :price_GE',   //价格大于等于， 单位为元
'originPrice > :originPrice_GT',
'originPrice >= :originPrice_GE', //原价大于等于， 单位为元
'originPrice = :originPrice',
'coinPrice > :coinPrice_GT',
'coinPrice = :coinPrice',
'originCoinPrice > :originCoinPrice_GT',
'originCoinPrice = :originCoinPrice',
'title LIKE :titleLike',
'courseSetTitle LIKE :courseSetTitleLike',
'userId = :userId',
'recommended = :recommended',
'createdTime >= :startTime',
'createdTime < :endTime',
'rating > :ratingGreaterThan',
'vipLevelId >= :vipLevelIdGreaterThan',
'vipLevelId = :vipLevelId',
'categoryId = :categoryId',
'smallPicture = :smallPicture',
'categoryId IN ( :categoryIds )',
'vipLevelId IN ( :vipLevelIds )',
'parentId = :parentId',
'parentId > :parentId_GT',
'parentId IN ( :parentIds )',
'id NOT IN ( :excludeIds )',
'id IN ( :courseIds )',
'id IN ( :ids)',
'locked = :locked',
'lessonNum > :lessonNumGT',
'orgCode = :orgCode',
'orgCode LIKE :likeOrgCode',