<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <div class="find-navbar"><i class="h5-icon h5-icon-houtui"></i>EduSoho 微网校</div>

    <div class="find-body">
      <setting-module v-for="(module, index) in modules"
                      :module="module" :active="isActive(module)"
                      :key="index"></setting-module>
    </div>

    <div class="find-footer">
      <div class="find-footer-item" v-for="item in items"
          :class="{ active: item.name === 'find' }"
          :style="footerItemStyle"
          :key="item.type">
        <img class="find-footer-item__icon" :src="item.name === 'find' ? item.active : item.normal" />
        <span class="find-footer-item__text">{{ item.type }}</span>
      </div>
    </div>
  </div>
</template>
<script>
import items from '@/utils/footer-config'
import Api from '@admin/api';
import settingModule from './setting-module';

export default {
  components: {
    settingModule
  },
  data() {
    return {
      title: 'EduSoho 微网校',
      modules: [
        {
          "type": "slide_show",
          "moduleType": "slide-1",
          "data": [{
              "title": "13岁少女身残志坚学习挖掘机",
              "image": {
                "id": "48",
                "uri": "http://www.esdev.com/files/tmp/2018/07-08/12015642a7e6573176.jpg",
                "size": "513241",
                "createdTime": "2018-07-08T12:01:56+08:00"
              },
              "link": {
                "type": "url",
                "target": null,
                "url": "http://www.esdev.com"
              }
            },
            {
              "title": "13岁少女身残志坚学习挖掘机",
              "image": {
                "id": "48",
                "uri": "http://www.esdev.com/files/tmp/2018/07-08/12015642a7e6573176.jpg",
                "size": "513241",
                "createdTime": "2018-07-08T12:01:56+08:00"
              },
              "link": {
                "type": "course",
                "target": {
                  "id": "1",
                  "title": "三月班"
                },
                "url": "http://www.esdev.com"
              }
            }
          ]
        },
        {
          "type": "course_list",
          "moduleType": "courseList-1",
          "data": {
            "title": "热门课程1",
            "sourceType": "condition",
            "categoryId": "1",
            "sort": "-studentNum",
            "startTime": "1533052800",
            "endTime": "1534694400",
            "limit": "4",
            "items": []
          }
        },
        {
          "type": "course_list",
          "moduleType": "courseList-2",
          "data": {
            "title": "热门课程2",
            "sourceType": "custom",
            "categoryId": "0",
            "sort": "",
            "startTime": "0",
            "endTime": "0",
            "limit": "4",
            "items": [{
                "id": "1",
                "title": "默认教学计划",
                "courseSetTitle": "试卷未去除已删除题目数量",
                "displayedTitle": "试卷未去除已删除题目数量-默认教学计划",
                "learnMode": "freeMode",
                "summary": "",
                "goals": [],
                "audiences": [],
                "isDefault": "1",
                "maxStudentNum": "0",
                "status": "published",
                "isFree": "1",
                "price": "0.00",
                "vipLevelId": "0",
                "buyable": "1",
                "tryLookable": "0",
                "tryLookLength": "0",
                "watchLimit": "0",
                "services": [],
                "taskNum": "6",
                "studentNum": "0",
                "parentId": "0",
                "ratingNum": "0",
                "rating": "0",
                "originPrice": "0.00",
                "buyExpiryTime": "0",
                "enableFinish": "1",
                "compulsoryTaskNum": "6",
                "createdTime": "2018-06-22T17:12:29+08:00",
                "updatedTime": "2018-07-05T09:49:27+08:00",
                "creator": {
                  "id": "2",
                  "nickname": "测试管理员",
                  "title": "老师",
                  "uuid": "974ac7c130a662c8788477d03afb8ade11a78852",
                  "avatar": {
                    "small": "http://www.esdev.com/files/default/2018/06-22/170813dd8832946730.jpg",
                    "middle": "http://www.esdev.com/files/default/2018/06-22/170813dd81c6850869.jpg",
                    "large": "http://www.esdev.com/files/default/2018/06-22/170813dd7b20643975.jpg"
                  }
                },
                "subtitle": "",
                "teachers": [{
                  "id": "2",
                  "nickname": "测试管理员",
                  "title": "老师",
                  "uuid": "974ac7c130a662c8788477d03afb8ade11a78852",
                  "avatar": {
                    "small": "http://www.esdev.com/files/default/2018/06-22/170813dd8832946730.jpg",
                    "middle": "http://www.esdev.com/files/default/2018/06-22/170813dd81c6850869.jpg",
                    "large": "http://www.esdev.com/files/default/2018/06-22/170813dd7b20643975.jpg"
                  }
                }],
                "courseSet": {
                  "id": "1",
                  "type": "normal",
                  "title": "试卷未去除已删除题目数量",
                  "subtitle": "",
                  "summary": "",
                  "cover": {
                    "large": "http://www.esdev.com/files/default/2018/06-25/16350154c47d811726.jpg",
                    "middle": "http://www.esdev.com/files/default/2018/06-25/16350154cac6482938.jpg",
                    "small": "http://www.esdev.com/files/default/2018/06-25/16350154d005583117.jpg"
                  },
                  "studentNum": "0",
                  "discount": "10.00",
                  "maxCoursePrice": "0.00",
                  "minCoursePrice": "0.00",
                  "minCoursePrice2": {
                    "currency": "RMB",
                    "amount": "0.00",
                    "coinAmount": "0",
                    "coinName": "虚拟币"
                  },
                  "maxCoursePrice2": {
                    "currency": "RMB",
                    "amount": "0.00",
                    "coinAmount": "0",
                    "coinName": "虚拟币"
                  }
                },
                "learningExpiryDate": {
                  "expiryMode": "forever",
                  "expiryStartDate": "0",
                  "expiryEndDate": "0",
                  "expiryDays": "0",
                  "expired": false
                },
                "price2": {
                  "currency": "RMB",
                  "amount": "0.00",
                  "coinAmount": "0",
                  "coinName": "虚拟币"
                },
                "originPrice2": {
                  "currency": "RMB",
                  "amount": "0.00",
                  "coinAmount": "0",
                  "coinName": "虚拟币"
                },
                "publishedTaskNum": "6"
              },
            ]
          }
        },
        {
          "type": "poster",
          "moduleType": "poster-1",
          "data": {
            "image": {
              "id": "48",
              "uri": "http://www.esdev.com/files/tmp/2018/07-08/12015642a7e6573176.jpg",
              "size": "513241",
              "createdTime": "2018-07-08T12:01:56+08:00"
            },
            "link": {
              "type": "url",
              "target": null,
              "url": "http://www.esdev.com"
            }
          }
        },
      ],
      currentModule: 'slide-1',
      items,
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100/items.length}%` }
    }
  },
  created() {
    // Api.saveDraftDate({
    //     query: {
    //       portal: 'h5',
    //       type: 'discovery'
    //     },
    //     params: {
    //       mode: 'draft'
    //     }
    //   })
    //   .then((data) => {
    //     console.log(data)
    //     this.parts = data;
    //   })
    //   .catch((err) => {
    //     console.log(err, 'error');
    //   });
  },
  methods: {
    isActive(module) {
      return module.moduleType === this.currentModule;
    }
  }
}

</script>
