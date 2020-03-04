<template>
  <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
    <van-list
      v-model="loading"
      :finished="finished"
      @load="onLoad">
      <!-- 公开课 -->
      <template  v-if="typeList=='open_course_list'">
        <div v-for="(date,index) in openCourseDate" :key="'date'+index">
          <div class="open_course_date van-hairline--bottom">{{date}}</div>
          <opencourseItem 
            v-for="(course, index) in openCourseList[date]"
            :key="'opencourse'+index"
            :type="courseItemType"
            :type-list="typeList"
            :is-app-use="isAppUse"
            :course="course"
          />
        </div>
      </template>
      <!-- 班级和课程 -->
      <template  v-else>
        <courseItem
          v-for="(course, index) in courseList"
          :key="index"
          :type="courseItemType"
          :normal-tag-show="normalTagShow"
          :vip-tag-show="vipTagShow"
          :type-list="typeList"
          :is-vip="course.vipLevelId"
          :is-app-use="isAppUse"
          :discountType="typeList === 'course_list' ? course.courseSet.discountType : ''"
          :discount="typeList === 'course_list' ? course.courseSet.discount : ''"
          :course-type="typeList === 'course_list' ? course.courseSet.type : ''"
          :course="course | courseListData(listObj,'appSetting')"
        />
      </template>
    </van-list>
  </van-pull-refresh>
</template>

<script>
import courseItem from '../e-row-class/e-row-class.vue'
import opencourseItem from '../e-openCourse-class/e-openCourse-more.vue'
import courseListData from '@/utils/filter-course.js'
import { mapState } from 'vuex'

export default {
  components: {
    courseItem,
    opencourseItem
  },

  filters: {
    courseListData
  },

  props: {
    courseList: Array,
    isRequestCompile: Boolean,
    isAllData: Boolean,
    isAppUse:Boolean,
    courseItemType: {
      type: String,
      default: ''
    },
    typeList: {
      type: String,
      default: 'course_list'
    },
    openCourseDate:{
      type: Array,
      default: ()=>[]
    },
    openCourseList:{
      type: Object,
      default: ()=>{}
    },
    normalTagShow: {
      type: Boolean,
      default: true
    },
    vipTagShow: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      list: [],
      finished: false,
      refreshing:false
    }
  },

  computed: {
    ...mapState(['courseSettings']),
    loading: {
      get() {
        return !this.isRequestCompile
      },
      set(v) {
        console.log(v, 'value')
      }
    },
    listObj() {
      return {
        type: this.courseItemType,
        typeList: this.typeList,
        showStudent: this.courseSettings
          ? Number(this.courseSettings.show_student_num_enabled) : true
      }
    }
  },

  watch: {
    isAllData() {
      this.loading = false
      this.finished = this.isAllData
    }
  },

  methods: {
    onLoad() {
      if (this.refreshing) {
          this.$emit('resetData')
          this.refreshing = false;
        }
      // 通知父组件请求数据并更新courseList
      if (this.isRequestCompile) this.$emit('needRequest')
    },
    onRefresh() {
      // 清空列表数据
      this.finished = false;
      // 重新加载数据
      // 将 loading 设置为 true，表示处于加载状态
      this.loading = true;
      this.onLoad();
    }
  }
}
</script>
