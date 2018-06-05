# 微网校接口需求

## 获取发现页信息 [New]

发现页的信息是后台可配置的，目前有3个模块，小程序端/H5端，可独立配置。

```
GET /discoveries/{h5,mp}
```

Response:

```json
[
    {
        "class": "slideshow",
        "data": [
            {
                "image": "//example.com/1.jpg",
                "link": {
                    "type": "url",
                    "url": "//example.com/1.html"
                }
            },
            {
                "image": "//example.com/1.jpg",
                "link": {
                    "type": "course",
                    "id": 1
                }
            }
        ]
    },
    {
        "class": "courselist",
        "data": {
            "title": "热门课程",
            "items": [
                {
                    "id": 1,
                    "image": "//example.com/course.jpg",
                    "title": "课程标题",
                    "about": "课程简介",
                    "price": "0.01",
                    "memberNum": 100
                },
                {
                    "id": 2,
                    "image": "//example.com/course.jpg",
                    "title": "课程标题",
                    "about": "课程简介",
                    "price": "0.01",
                    "memberNum": 100
                }
            ],
            "conditions": {
                "categoryId": 1
            },
            "sort": "mostMembers / lastCreated / highScort",
            "more": true
        }
    },
    {
        "class": "image",
        "data": {
            "image": "//example.com/course.jpg",
            "link": {
                "type": "course",
                "id": 100
            }
        }
    }
]
```

## 我的学习页

已有接口：
```
GET /me/courses
```
参见：
http://developer.edusoho.com/api/user.html


## 我的订单 [New]

```
GET /me/orders?type=course
```

只获取我的课程的订单。

Response:

```json
{
    "data": [
        {...OrderInfo...},
        {...OrderInfo...},
        {...OrderInfo...},
    ],
    "paging": {
        "total": 100,
        "offset": 50,
        "limit": 10
    }
}
```
OrderInfo结构体，参见： http://developer.edusoho.com/api/order.html

## 获取我的个人信息

```
GET /me
```

参见：
http://developer.edusoho.com/api/user.html


## 修改头像  [New]

```
POST /me/avatar
```

{
  "image": "base64之后的数据"
}

头像上传之前，先本地缩小到xx*xx像素，jpg格式。具体指标，产品经理给出。

