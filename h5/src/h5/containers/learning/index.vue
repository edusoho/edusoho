<template>
  <div class="e-learn">
    <van-tabs
      v-model="active"
      class="after-tabs">
      <van-tab
        v-for="item in tabs"
        :title="item"
        :key="item"/>
    </van-tabs>
    <emptyCourse v-if="active==0 && isEmptyCourse && isCourseFirstRequestCompile" :type="typeList"/>
    <emptyCourse v-if="active==1 && isEmptyClass && isClassFirstRequestCompile" :type="typeList"/>
    <emptyCourse v-if="active==2 && isEmptyBank && isBankFirstRequestCompile" :type="typeList" :text="bank"/>
    <div v-else>
      <lazyLoading
        v-show="active==0"
        :course-list="courseList"
        :normal-tag-show="false"
        :is-all-data="isAllCourse"
        :course-item-type="courseItemType"
        :is-request-compile="isCourseRequestComplete"
        :type-list="'course_list'"
        @needRequest="courseSendRequest"
      />
      <lazyLoading
        v-show="active==1"
        :course-list="classList"
        :is-all-data="isAllClass"
        :normal-tag-show="false"
        :course-item-type="classItemType"
        :is-request-compile="isClassRequestComplete"
        :type-list="'classroom_list'"
        @needRequest="classSendRequest"
      />
      <infinite-scroll
        v-show="active==2"
        :course-list="bankList"
        :is-all-data="isAllBank"
        :normal-tag-show="false"
        :course-item-type="bankItemType"
        :is-request-compile="isBankRequestComplete"
        :type-list="'item_bank_exercise'"
        @needRequest="bankSendRequest"
      />
    </div>
  </div>
</template>
<script>
import emptyCourse from './emptyCourse/emptyCourse.vue'
import lazyLoading from '&/components/e-lazy-loading/e-lazy-loading.vue'
import infiniteScroll from '&/components/e-infinite-scroll/e-infinite-scroll.vue'
import Api from '@/api'
import preloginMixin from '@/mixins/preLogin'

export default {
  components: {
    emptyCourse,
    lazyLoading,
    infiniteScroll,
  },
  mixins: [preloginMixin],
  data() {
    return {
      courseItemType: 'rank',
      classItemType: 'rank',
      bankItemType: 'rank',
      isEmptyCourse: true,
      isEmptyClass: true,
      isEmptyBank: true,
      isCourseRequestComplete: false,
      isClassRequestComplete: false,
      isBankRequestComplete: false,
      isAllCourse: false,
      isAllClass: false,
      isAllBank: false,
      courseList: [],
      classList: [],
      bankList: [],
      offset_course: 0,
      offset_class: 0,
      offset_bank: 0,
      limit_course: 10,
      limit_class: 10,
      limit_bank: 10,
      active: 0,
      isCourseFirstRequestCompile: false,
      isClassFirstRequestCompile: false,
      isBankFirstRequestCompile: false,
      tabs: ['我的课程', '我的班级', '我的题库'],
      bank: '题库',
    }
  },
  computed: {
    typeList() {
      if (this.active === 0) {
        return 'course_list';
      } else if (this.active === 1) {
        return 'classroom_list';
      } else {
        return 'item_bank_exercise';
      }
    },
    // return this.active == 0 ? 'course_list' : 'classroom_list'
  },

  created() {
    const courseSetting = {
      offset: this.offset_course,
      limit: this.limit_course
    }
    const classSetting = {
      offset: this.offset_class,
      limit: this.limit_class
    }
    const bankSetting = {
      offset: this.offset_bank,
      limit: this.limit_bank
    }

    this.requestCourses(courseSetting)
      .then(() => {
        this.isCourseFirstRequestCompile = true
        if (this.courseList.length !== 0) {
          this.isEmptyCourse = false
        } else {
          this.isEmptyCourse = true
        }
      })
    this.requestClasses(classSetting)
      .then(() => {
        this.isClassFirstRequestCompile = true
        if (this.classList.length !== 0) {
          this.isEmptyClass = false
        } else {
          this.isEmptyClass = true
        }
      })
    this.requestBanks(bankSetting)
      .then(() => {
        this.isBankFirstRequestCompile = true
        if (this.bankList.length !== 0) {
          this.isEmptyBank = false
        } else {
          this.isEmptyBank = true
        }
      })
  },
  methods: {
    judgeIsAllCourse(courseInfomation) {
      return this.courseList.length == courseInfomation.paging.total
    },

    judgeIsAllClass(classInfomation) {
      return this.classList.length == classInfomation.paging.total
    },

    judgeIsAllBank(bankInfomation) {
      return this.bankList.length == bankInfomation.paging.total
    },

    requestCourses(setting) {
      this.isCourseRequestComplete = false
      return Api.myStudyCourses({
        params: setting
      }).then((data) => {
        let isAllCourse
        if (!isAllCourse) {
          this.courseList = [...this.courseList, ...data.data]
          this.offset_course = this.courseList.length
        }

        isAllCourse = this.judgeIsAllCourse(data)
        this.isAllCourse = isAllCourse
        this.isCourseRequestComplete = true
      }).catch((err) => {
        console.log(err, 'error')
      })
    },

    requestClasses(setting) {
      this.isClassRequestComplete = false
      return Api.myStudyClasses({
        params: { ...setting, format: 'pagelist' }
      }).then((data) => {
        let isAllClass
        if (!isAllClass) {
          this.classList = [...this.classList, ...data.data]
          this.offset_class = this.classList.length
        }

        isAllClass = this.judgeIsAllClass(data)
        this.isAllClass = isAllClass
        this.isClassRequestComplete = true
      }).catch((err) => {
        console.log(err, 'error')
      })
    },

    requestBanks(setting) {
      this.isBankRequestComplete = false
      return Api.myStudyBanks({
        params: { ...setting, format: 'pagelist' }
      }).then((data) => {
        let isAllBank
        if (!isAllBank) {
          this.bankList = [...this.bankList, ...data.data]
          this.offset_bank = this.bankList.length
        }

        isAllBank = this.judgeIsAllBank(data)
        this.isAllBank = isAllBank
        this.isBankRequestComplete = true
      }).catch((err) => {
        console.log(err, 'error')
      })
    },

    courseSendRequest() {
      const args = {
        offset: this.offset_course,
        limit: this.limit_course
      }
      if (!this.isAllCourse) this.requestCourses(args)
    },

    classSendRequest() {
      const args = {
        offset: this.offset_class,
        limit: this.limit_class
      }
      if (!this.isAllClass) this.requestClasses(args)
    },

    bankSendRequest() {
      const args = {
        offset: this.offset_bank,
        limit: this.limit_bank
      }
      if (!this.isAllBank) this.requestBanks(args)
    }
  }
}

</script>

<style>
  .e-learn {
    padding-bottom: 60px;
  }
</style>
