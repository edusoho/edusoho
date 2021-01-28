# 获取教学计划信息

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
'buyable = :buyable',
'concat(courseSetTitle, title) like :courseOrCourseSetTitleLike',