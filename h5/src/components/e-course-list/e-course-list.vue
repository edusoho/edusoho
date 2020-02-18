<template>
  <!-- 详见showMode注释 -->
  <div v-if="(showMode==='h5' && courseList.items.length) || showMode==='admin'" class="e-course-list">
    <div v-if="pathName!=='appSetting'" class="e-course-list__header" >
      <div class="clearfix">
        <span class="e-course-list__list-title text-overflow">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <span class="more-text pull-left" @click="jumpTo(courseList.source)">更多</span>
        </span>
      </div>
    </div>
    <!-- 现在style中写样式，后续三端统一后再把上面那段div干掉，把style的样式写入到class中 -->
    <div v-if="pathName==='appSetting'" class="e-course-list__header" style="padding:16px">
      <div class="clearfix">
        <span class="e-course-list__list-title text-overflow" style="font-size:16px">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <span class="more-text pull-left" style="font-size:12px" @click="jumpTo(courseList.source)">更多</span>
        </span>
      </div>
    </div>
    <div v-if="courseList.items.length">
      <div class="e-course-list__body">
        <e-class
          v-for="item in courseList.items"
          v-if="pathName!=='appSetting'"
          :key="item.id"
          :course="item | courseListData(listObj)"
          :discount="typeList === 'course_list' ? item.courseSet.discount : ''"
          :course-type="typeList === 'course_list' ? item.courseSet.type : ''"
          :type-list="typeList"
          :normal-tag-show="normalTagShow"
          :vip-tag-show="vipTagShow"
          :type="type"
          :is-vip="item.vipLevelId"
          :feedback="feedback"/>
        <!-- 一行一列  目前只正对app -->
        <e-row-class
          v-for="item in courseList.items"
          v-if="pathName==='appSetting' && courseList.displayStyle==='row'"
          :key="item.id"
          :course="item | courseListData(listObj,pathName)"
          :discount="typeList === 'course_list' ? item.courseSet.discount : ''"
          :course-type="typeList === 'course_list' ? item.courseSet.type : ''"
          :type-list="typeList"
          :normal-tag-show="normalTagShow"
          :vip-tag-show="vipTagShow"
          :type="type"
          :is-vip="item.vipLevelId"
          :feedback="feedback"
        />
        <!-- 一行两列  目前只正对app -->
        <div v-if="pathName==='appSetting' && courseList.displayStyle==='distichous'" class="clearfix">
          <e-column-class
            v-for="item in courseList.items"
            :key="item.id"
            :course="item | courseListData(listObj,pathName)"
            :discount="typeList === 'course_list' ? item.courseSet.discount : ''"
            :course-type="typeList === 'course_list' ? item.courseSet.type : ''"
            :type-list="typeList"
            :normal-tag-show="normalTagShow"
            :vip-tag-show="vipTagShow"
            :type="type"
            :is-vip="item.vipLevelId"
            :feedback="feedback"
          />
        </div>
      </div>
      <div v-show="courseItemData" class="e-course__empty">暂无{{ typeList === 'course_list' ? '课程' : '班级' }}</div>
    </div>
  </div>
</template>

<script>
import eClass from '../e-class/e-class'
import eRowClass from '../e-row-class/e-row-class'
import eColumnClass from '../e-column-class/e-column-class'
import courseListData from '@/utils/filter-course.js'
import { mapState } from 'vuex'

export default {
  components: {
    'e-class': eClass,
    'e-row-class': eRowClass,
    'e-column-class': eColumnClass
  },
  filters: {
    courseListData
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
    typeList: {
      type: String,
      default: 'course_list'
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
    vipName: {
      type: String,
      default: '会员'
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
  data() {
    return {
      type: 'price'
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
    jumpTo(source) {
      if (!this.feedback) {
        return
      }
      if (this.moreType === 'vip') {
        this.$router.push({
          name: this.typeList === 'course_list' ? 'vip_course' : 'vip_classroom',
          query: {
            vipName: this.vipName,
            levelId: this.levelId
          }
        })
      } else {
        this.$router.push({
          name: this.typeList === 'course_list' ? 'more_course' : 'more_class'
        })
      }
    },
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
