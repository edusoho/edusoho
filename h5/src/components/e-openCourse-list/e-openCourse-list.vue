<template>
  <!-- 详见showMode注释 -->
  <div v-if="(showMode==='h5' && courseList.items.length) || showMode==='admin'" class="e-course-list">
    <div  class="e-course-list__header" style="padding:16px">
      <div class="clearfix">
        <span class="e-course-list__list-title text-overflow" style="font-size:16px">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <span class="more-text pull-left" style="font-size:12px" @click="jumpTo(courseList.source)">更多</span>
        </span>
      </div>
    </div>
    <div v-if="courseList.items.length">
      <div class="e-course-list__body nowarp prl16">
          <e-openCourse-class
            v-for="item in courseList.items"
            :key="item.id"
            :course="item"
            :feedback="feedback"
          />
      </div>
      <div v-show="courseItemData" class="e-course__empty">暂无公开课</div>
    </div>
  </div>
</template>

<script>
import eOpenCourseClass from '../e-openCourse-class/e-openCourse-class'
import courseListData from '@/utils/filter-course.js'
import { mapState } from 'vuex'

export default {
  components: {
    'e-openCourse-class': eOpenCourseClass
  },
  props: {
    courseList: {
      type: Object,
      default: () => {}
    },
    feedback: {
      type: Boolean,
      default: true
    },
    index: {
      type: Number,
      default: -1
    },
    normalTagShow: {
      type: Boolean,
      default: true
    },
    vipTagShow: {
      type: Boolean,
      default: false
    },
    moreType: {
      type: String,
      default: 'normal'
    },

    levelId: {
      type: Number,
      default: 1
    },
    showMode: {// 展示模式  h5、admin 在admin模式下就算列表length为0，也要展示出列表标题。在h5模式下，length为0不展示任何列表标题
      type: String,
      default: 'h5'
    }
  },
  filters: {
    courseListData
  },
  data() {
    return {
    }
  },
  computed: {
    ...mapState(['courseSettings', 'classroomSettings']),
    sourceType: {
      get() {
        return this.courseList.sourceType
      }
    },
    sort: {
      get() {
        return this.courseList.sort
      }
    },
    lastDays: {
      get() {
        return this.courseList.lastDays
      }
    },
    limit: {
      get() {
        return this.courseList.limit
      }
    },
    categoryId: {
      get() {
        return this.courseList.categoryId
      }
    },
    courseItemData: {
      get() {
        return !this.courseList.items.length
      },
      set() {}
    },
    pathName: {
      get() {
        if (this.$route.name === 'appSetting' || this.$route.query.from === 'appSetting') {
          return 'appSetting'
        }
        return this.$route.name
      },
      set() {}
    },
    listObj() {
      return {
        type: 'price',
        typeList: this.typeList,
        showStudent: this.courseSettings
          ? Number(this.courseSettings.show_student_num_enabled) : true,
        classRoomShowStudent: this.classroomSettings
          ? this.classroomSettings.show_student_num_enabled : true
      }
    }
  },
  watch: {
    sort(value) {
      this.fetchCourse()
    },
    limit(value, oldValue) {
      if (oldValue > value) {
        const newItems = this.courseList.items.slice(0, value)
        this.courseList.items = newItems
        return
      }
      this.fetchCourse()
    },
    lastDays(value) {
      this.fetchCourse()
    },
    categoryId(value) {
      this.fetchCourse()
    },
    sourceType(value, oldValue) {
      if (value !== oldValue) {
        this.courseList.items = []
      }
      this.fetchCourse()
    }
  },

  created() {
    if (!this.pathName.includes('Setting')) return
    this.fetchCourse()
  },
  methods: {
    fetchCourse() {
      if (this.sourceType === 'custom') return

      const params = {
        sort: this.sort,
        limit: this.limit,
        lastDays: this.lastDays,
        categoryId: this.categoryId
      }

      this.$emit('fetchCourse', {
        index: this.index,
        params,
        typeList: this.typeList
      })
    }
  }
}
</script>
