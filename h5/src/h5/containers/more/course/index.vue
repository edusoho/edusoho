<template>
  <div :class="{ 'more__still': selecting }" class="more">
    <treeSelect
      :select-items="selectItems"
      v-model="selectedData"
      @selectedChange="setQuery"
      @selectToggled="toggleHandler"
    />
    <lazyLoading
      :course-list="courseList"
      :is-all-data="isAllCourse"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :vip-tag-show="true"
      :type-list="'course_list'"
      @needRequest="sendRequest"
    />
    <emptyCourse v-if="isEmptyCourse && isRequestCompile" :has-button="false" :type="'course_list'"/>
  </div>
</template>

<script>
import Api from '@/api'
import treeSelect from '&/components/e-tree-select/e-tree-select.vue'
import lazyLoading from '&/components/e-lazy-loading/e-lazy-loading.vue'
import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue'
import { mapState, mapActions } from 'vuex'
import * as types from '@/store/mutation-types'
import CATEGORY_DEFAULT from '@/config/category-default-config.js'

export default {
  components: {
    treeSelect,
    lazyLoading,
    emptyCourse
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
  computed: {
    ...mapState({
      searchCourseList: state => state.course.searchCourseList
    }),
  },
  watch: {
    selectedData() {
      const { courseList, selectedData } = this.searchCourseList;

      if (this.isSelectedDataSame(selectedData)) {
        this.courseList = courseList;
        this.isRequestCompile = true;

        return;
      }

      this.initCourseList()
      const setting = {
        offset: this.offset,
        limit: this.limit
      }

      this.requestCourses(setting)
        .then(() => {
          if (this.courseList.length !== 0) {
            this.isEmptyCourse = false
          } else {
            this.isEmptyCourse = true
          }
        })
    }
  },
  created() {
    this.selectedData = this.transform(this.$route.query)
    // 合并参数
    const config = Object.assign({}, this.selectedData, {
      offset: this.offset,
      limit: this.limit
    })

    // 获取班级分类数据
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
  methods: {
    ...mapActions('course', [
      'setCourseList',
    ]),
    setQuery(value) {
      this.$router.replace({
        name: 'more_course',
        query: value,
      })
    },

    initCourseList() {
      this.isRequestCompile = false
      this.isAllCourse = false
      this.courseList = []
      this.offset = 0
    },

    judegIsAllCourse(courseInfomation) {
      if (this.courseList.length == courseInfomation.paging.total) {
        return true
      }
      return false
    },

    requestCourses(setting) {
      this.isRequestCompile = false
      const config = Object.assign({}, this.selectedData, setting)
      return Api.getCourseList({
        params: config
      }).then((data) => {
        data.data.forEach(element => {
          this.courseList.push(element)
        })
        this.setCourseList({
          selectedData: this.selectedData,
          courseList: this.courseList
        });
        const isAllCourse = this.judegIsAllCourse(data)
        if (!isAllCourse) {
          this.offset = this.courseList.length
        }
        this.isAllCourse = isAllCourse
        this.isRequestCompile = true
      }).catch((err) => {
        console.log(err, 'error')
      })
    },

    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit
      }

      if (!this.isAllCourse) this.requestCourses(args)
    },

    transform(obj = {}) {
      return Object.assign({
        categoryId: this.categoryId,
        type: this.type,
        sort: this.sort
      }, obj);
    },
    toggleHandler(value) {
      this.selecting = value
    },
    isSelectedDataSame(selectedData) {
      for (const key in this.selectedData) {
        if (this.selectedData[key] !== selectedData[key]) {
          return false;
        }
      }

      return true;
    }
  }
}
</script>
