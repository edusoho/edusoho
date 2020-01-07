<template>
<div>
  <div class="nav-bar">
      <i class="iconfont icon-arrow-left"></i>
      <div class="nav-bar-title">所有班级</div>
  </div>

  <div class="default">
    <span class="active-bg">全部</span>
  </div>

  <infinite-scroll
      :course-list="courseList"
      :is-all-data="true"
      :normal-tag-show="false"
      :vip-tag-show="true"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :type-list="'classroom_list'"
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
  name:'more_class_new',
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
      this.getClassList()
    }
  },
  created() {
    this.selectedData = this.transform(this.$route.query)
    this.getClassCategories()
  },
  methods: {
    // 获取班级分类数据
    getClassCategories() {
       Api.getClassCategories()
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
    getClassList(){
      const setting = {
        offset: this.offset,
        limit: this.limit
      }

      this.requestClass(setting)
        .then(() => {
          this.isEmptyCourse= (this.courseList.length === 0)
        })
    },
    judegIsAllCourse(courseInfomation) {
      return this.courseList.length == courseInfomation.paging.total
    },
    requestClass(setting) {
      this.isRequestCompile = false
      const config = Object.assign(this.selectedData, setting)
      return Api.getClassList({
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

      if (!this.isAllCourse) this.requestClass(args)
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