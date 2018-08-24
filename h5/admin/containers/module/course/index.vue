<template>
  <div class="setting-carousel setting-course active">
    <div class="find-page__part">
      <e-course-list :courseList="courseList" :maxNum="maxNum" :feedback="false"></e-course-list>
    </div>
    <div class="carousel-allocate course-allocate">
      <header class="title">课程列表设置</header>
      <div class="carousel-item-setting course-item-setting clearfix">
        <!-- 列表名称 -->
        <div class="course-item-setting__section">
          <p class="pull-left section-left">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="courseList.title" placeholder="请输入列表名称" clearable></el-input>
          </div>
        </div>
        <!-- 课程来源 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程来源：</p>
          <div class="section-right">
            <el-radio v-model="radio" label="category">课程分类</el-radio>
            <el-radio v-model="radio" label="custom">自定义</el-radio>
          </div>
        </div>
        <!-- 课程分类 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程分类：</p>
          <div class="section-right">
            <el-input v-if="radio === 'category'" size="mini" v-model="category" :focus="focused" readOnly placeholder="请输入列表名称"></el-input>
            <div v-show="radio === 'custom'">
              <button>选择课程</button>
            </div>
          </div>
        </div>
        <!-- 排列顺序 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <div class="section-right__item pull-left">
              <el-select v-model="sortSelected" placeholder="排列顺讯" size="mini">
                <el-option v-for="item in sortOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="section-right__item pull-right">
              <el-select v-model="date" placeholder="时间区间" size="mini">
                <el-option v-for="item in dateOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <!-- 显示个数 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">显示个数：</p>
          <div class="section-right">
            <el-select v-model="maxNum" placeholder="请选择个数" size="mini">
              <el-option v-for="item in maxNumOptions" :key="item.value" :label="item.label" :value="item.value">
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
      maxNumOptions: [
        {
        value: 1,
        label: 1,
        }, {
          value: 2,
          label: 2,
        }, {
          value: 3,
          label: 3,
        }, {
          value: 4,
          label: 4,
        }, {
          value: 5,
          label: 5,
        }
      ],
      maxNum: 1,
      sortSelected: '热度',
      sortOptions: [
        {
          value: '-hitNum',
          label: '热度'
        }, {
          value: '-recommend',
          label: '推荐'
        }
      ],
      category: '音乐',
      categoryOptions: [
        {
          value: '音乐',
          label: '音乐',
        }, {
          value: '数学',
          label: '数学',
        }
      ],
      date: '近7天',
      dateOptions: [
        {
          value: '7',
          label: '近7天',
        }, {
          value: '30',
          label: '近30天',
        }
      ],
      radio: 'custom',
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
          "buyable": "1",
          "tryLookable": "0",
          "tryLookLength": "0",
          "watchLimit": "0",
          "taskNum": "6",
          "studentNum": "0",
          "parentId": "0",
          "originPrice": "0.00",
          "buyExpiryTime": "0",
          "enableFinish": "1",
          "compulsoryTaskNum": "6",
          "subtitle": "",
          "courseSet": {
            "id": "1",
            "type": "normal",
            "title": "试卷未去除已删除题目数量",
            "subtitle": "",
            "summary": "",
            "cover": {
              "large": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg",
              "middle": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg",
              "small": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg"
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
        }, {
          "id": "2",
          "title": "默认教学计划",
          "courseSetTitle": "测试课程2",
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
          "buyable": "1",
          "tryLookable": "0",
          "tryLookLength": "0",
          "watchLimit": "0",
          "taskNum": "6",
          "studentNum": "0",
          "parentId": "0",
          "originPrice": "0.00",
          "buyExpiryTime": "0",
          "enableFinish": "1",
          "compulsoryTaskNum": "6",
          "subtitle": "",
          "courseSet": {
            "id": "1",
            "type": "normal",
            "title": "试卷未去除已删除题目数量",
            "subtitle": "",
            "summary": "",
            "cover": {
              "large": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg",
              "middle": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg",
              "small": "http://t5.edusoho.cn/files/course/2017/02-23/104632835c59950208.jpg"
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
  },
  methods: {
    focused() {
      console.log(123123);
    }
  }
}

</script>
