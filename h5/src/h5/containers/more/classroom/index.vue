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
      :is-all-data="true"
      :normal-tag-show="false"
      :vip-tag-show="true"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :type-list="'classroom_list'"
      @needRequest="sendRequest"
    />
    <emptyCourse v-if="isEmptyCourse && isRequestCompile" :has-button="false" :type="'classroom_list'"　text="暂无班级"/>
  </div>
</template>

<script>
import Api from '@/api'
import treeSelect from '&/components/e-tree-select/e-tree-select.vue'
import lazyLoading from '&/components/e-lazy-loading/e-lazy-loading.vue'
import emptyCourse from '../../learning/emptyCourse/emptyCourse.vue'
import { mapState, mapMutations, mapActions } from 'vuex'
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
      copySelectItems: [],
      selectedData: {},
      courseItemType: 'price',
      isRequestCompile: false,
      isAllClassroom: false,
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
      dataDefault: CATEGORY_DEFAULT['classroom_list']
    }
  },
  computed: {
    ...mapState({
      searchClassRoomList: state => state.classroom.searchClassRoomList
    }),
  },
  watch: {
    selectedData() {
      const { courseList, selectedData, paging } = this.searchClassRoomList;

      if (this.isSelectedDataSame(selectedData)) {
        this.courseList = courseList;
        this.requestClassRoomSuccess(paging);

        return;
      }

      this.initCourseList()
      const setting = {
        offset: this.offset,
        limit: this.limit
      }

      this.requestCourses(setting);
    }
  },
  created() {
    window.scroll(0, 0);
    this.selectedData = this.transform(this.$route.query)
    // 合并参数
    const config = Object.assign({}, this.selectedData, {
      offset: this.offset,
      limit: this.limit
    })

    // 获取班级分类数据
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
  methods: {
    ...mapActions('classroom', [
      'setClassRoomList',
    ]),

    setQuery(value) {
      this.$router.replace({
        name: 'more_class',
        query: value,
      })
    },

    initCourseList() {
      this.isRequestCompile = false
      this.isAllClassroom = false
      this.courseList = []
      this.offset = 0
    },

    judegIsAllClassroom(paging) {
      return this.courseList.length == paging.total
    },

    requestCourses(setting) {
      this.isRequestCompile = false
      const config = Object.assign({}, this.selectedData, setting)
      return Api.getClassList({
        params: config
      }).then(({ data, paging }) => {
        data.forEach(element => {
          this.courseList.push(element)
        })
        this.setClassRoomList({
          selectedData: this.selectedData,
          courseList: this.courseList,
          paging,
        });
        this.requestClassRoomSuccess(paging);
      }).catch((err) => {
        console.log(err, 'error')
      })
    },

    requestClassRoomSuccess(paging = {}) {
      this.isAllClassroom = this.judegIsAllClassroom(paging)
      if (!this.isAllClassroom) {
        this.offset = this.courseList.length
      }
      this.isRequestCompile = true
      this.isEmptyCourse = this.courseList.length === 0
    },

    sendRequest() {
      const args = {
        offset: this.offset,
        limit: this.limit
      }

      if (!this.isAllClassroom) this.requestCourses(args)
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
        if (this.selectedData[key] != selectedData[key]) {
          return false;
        }
      }

      return true;
    }
  }
}
</script>
