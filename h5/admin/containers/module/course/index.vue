<template>
  <div class="setting-carousel setting-course">
    <div class="find-page__part">
      <e-course-list :courseList="courseList"></e-course-list>
    </div>
    <div class="carousel-allocate course-allocate">
      <header class="title">课程列表设置</header>
      <div class="carousel-item-setting course-item-setting clearfix">
        <div class="course-item-setting__section">
          <p class="pull-left section-left">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请输入列表名称"></el-input>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程来源：</p>
          <div class="section-right">
            <el-select v-model="value" placeholder="请选择分类" size="mini">
              <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程分类：</p>
          <div class="section-right">
            <el-select v-model="value" placeholder="请选择分类" size="mini">
              <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <div class="section-right__item pull-left">
              <el-select v-model="value" placeholder="排列顺讯" size="mini">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="section-right__item pull-right">
              <el-select v-model="value" placeholder="时间区间" size="mini">
                <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">显示个数：</p>
          <div class="section-right">
            <el-select v-model="value" placeholder="请选择个数" size="mini">
              <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import courseList from '@/containers/components/e-course-list/e-course-list.vue';

export default {
  components: {
    'e-course-list': courseList,
  },
  data() {
    return {
      input: '',
      options: [],
      value: '',
      courseList: {
        "title": "热门课程",
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
        }],
        "source": {
          "category": 0,
          "courseType": "all",
          "sort": "-hitNum"
        }
      }
    }
  }
}

</script>
