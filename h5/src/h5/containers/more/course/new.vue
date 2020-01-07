<template>
  <div>
    <div class="nav-bar">
        <i class="iconfont icon-arrow-left"></i>
        <div class="nav-bar-title">所有班级</div>
    </div>

    <infinite-scroll
      :course-list="courseList"
      :is-all-data="isAllCourse"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :vip-tag-show="true"
      :type-list="'course_list'"
      @needRequest="sendRequest"
    />
  </div>
</template>

<script>
import Api from '@/api'
import infiniteScroll from '&/components/e-infinite-scroll/e-infinite-scroll.vue'
import { mapMutations } from 'vuex'
import * as types from '@/store/mutation-types'
import CATEGORY_DEFAULT from '@/config/category-default-config.js'
export default {
  name:'more_course_new',
  components: {
    infiniteScroll
  },
  data() {
    return {
      selectItems: [],
      selectedData: {},
      courseItemType: 'price',
      isRequestCompile: false,
      isAllCourse: false,
      isEmptyCourse: true,
      courseList: [],
      offset: 0,
      limit: 10,
      type: 'all',
      categoryId: 0,
      sort: 'recommendedSeq',
      selecting: false,
      queryForm: {
        courseType: 'type',
        category: 'categoryId',
        sort: 'sort'
      },
      dataDefault: CATEGORY_DEFAULT['course_list']
    }
  },
  watch: {
    selectedData() {
      this.initCourseList()
      this.getCourseList()
    }
  },
  created() {
    this.selectedData = this.transform(this.$route.query)
    this.getCourseCategories()
  },
  methods: {
    // 获取课程分类数据
    getCourseCategories() {
       Api.getCourseCategories()
        .then((data) => {
          data.unshift({
            name: '全部',
            id: '0'
          })
          this.dataDefault[0].data = data
          this.selectItems = this.dataDefault
        })
    },
    initCourseList() {
      this.isRequestCompile = false
      this.isAllCourse = false
      this.courseList = []
      this.offset = 0
    },
    getCourseList(){
      const setting = {
        offset: this.offset,
        limit: this.limit
      }

      this.requestCourses(setting)
        .then(() => {
          this.isEmptyCourse= (this.courseList.length === 0)
        })
    },
    judegIsAllCourse(courseInfomation) {
      return this.courseList.length == courseInfomation.paging.total
    },
    requestCourses(setting) {
      this.isRequestCompile = false
      const config = Object.assign(this.selectedData, setting)
      return Api.getCourseList({
        params: config
      }).then((data) => {
        this.formateData(data)
        this.isRequestCompile = true
      }).catch((err) => {
        console.log(err, 'error')
      })
    },
    formateData(data){
        this.courseList=this.courseList.concat( data.data)
        this.isAllCourse = this.judegIsAllCourse(data)
        if (!this.isAllCourse) {
          this.offset = this.courseList.length
        }
    },
    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit
      }

      if (!this.isAllCourse) this.requestCourses(args)
    },
    transform(obj) {
      const config = {}
      const arr = Object.keys(obj)
      if (!arr.length) {
        return {
          categoryId: this.categoryId,
          type: this.type,
          sort: this.sort
        }
      }
      arr.forEach((current, index) => {
        config[this.queryForm[current]] = obj[current]
      })
      return config
    },
    toggleHandler(value) {
      this.selecting = value
    }
  }
}
</script>

<style>

</style>