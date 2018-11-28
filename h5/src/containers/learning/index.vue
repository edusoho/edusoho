<template>
  <div class="e-learn">
    <van-tabs
      v-model="active"
      class="after-tabs">
      <van-tab v-for="item in tabs"
        :title="item" :key="item"></van-tab>
    </van-tabs>
    <emptyCourse v-if="active==0 && isEmptyCourse && isCourseFirstRequestCompile" :type="typeList"></emptyCourse>
    <emptyCourse v-if="active==1 && isEmptyClass && isClassFirstRequestCompile" :type="typeList"></emptyCourse>
    <div v-else>
      <lazyLoading
        v-show="active==0"
        :courseList="courseList"
        :isAllData="isAllCourse"
        :courseItemType="courseItemType"
        :isRequestCompile="isCourseRequestComplete"
        @needRequest="courseSendRequest"
        :typeList="'course_list'"
      ></lazyLoading>
      <lazyLoading
        v-show="active==1"
        :courseList="classList"
        :isAllData="isAllClass"
        :courseItemType="classItemType"
        :isRequestCompile="isClassRequestComplete"
        @needRequest="classSendRequest"
        :typeList="'classroom_list'"
      ></lazyLoading>
    </div>
    <div class="mt50"></div>
  </div>
</template>
<script>
  import emptyCourse from './emptyCourse/emptyCourse.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';
  import Api from '@/api';
  import preloginMixin from '@/mixins/preLogin';

  export default {
    mixins: [preloginMixin],
    components: {
      emptyCourse,
      lazyLoading,
    },
    data() {
      return {
        courseItemType: 'rank',
        classItemType: 'rank',
        isEmptyCourse: true,
        isEmptyClass: true,
        isCourseRequestComplete: false,
        isClassRequestComplete: false,
        isAllCourse: false,
        isAllClass: false,
        courseList: [],
        classList: [],
        offset_course: 0,
        offset_class: 0,
        limit_course: 10,
        limit_class: 10,
        active: 0,
        isCourseFirstRequestCompile: false,
        isClassFirstRequestCompile: false,
        tabs: ['我的课程', '我的班级']
      };
    },
    computed: {
      typeList() {
        return this.active == 0 ? 'course_list' : 'classroom_list';
      }
    },
    methods: {
      judgeIsAllCourse(courseInfomation) {
        return this.courseList.length == courseInfomation.paging.total
      },

      judgeIsAllClass(classInfomation) {
        return this.classList.length == classInfomation.paging.total
      },

      requestCourses(setting) {
        this.isCourseRequestComplete = false;
        return Api.myStudyCourses({
          params: setting
        }).then((data) => {
          let isAllCourse;
          if (!isAllCourse) {
            this.courseList = [...this.courseList, ...data.data]
            this.offset_course = this.courseList.length
          }

          isAllCourse = this.judgeIsAllCourse(data);
          this.isAllCourse = isAllCourse;
          this.isCourseRequestComplete = true;
        }).catch((err) => {
          console.log(err, 'error');
        })
      },

      requestClasses(setting) {
        this.isClassRequestComplete = false;
        return Api.myStudyClasses({
          params: setting
        }).then((data) => {
          let isAllClass;
          if (!isAllClass) {
            this.classList = [...this.classList, ...data.data]
            this.offset_class = this.classList.length
          }

          isAllClass = this.judgeIsAllClass(data);
          this.isAllClass = isAllClass;
          this.isClassRequestComplete = true;
        }).catch((err) => {
          console.log(err, 'error');
        })
      },

      courseSendRequest() {
        const args = {
          offset: this.offset_course,
          limit: this.limit_course
        };
        if (!this.isAllCourse) this.requestCourses(args);
      },

      classSendRequest() {
        const args = {
          offset: this.offset_class,
          limit: this.limit_class
        };
        if (!this.isAllClass) this.requestClasses(args);
      }
    },

    created() {
      const courseSetting = {
        offset: this.offset_course,
        limit: this.limit_course
      };
      const classSetting = {
        offset: this.offset_class,
        limit: this.limit_class
      };

      this.requestCourses(courseSetting)
        .then(() => {
          this.isCourseFirstRequestCompile = true;
          if (this.courseList.length !== 0) {
            this.isEmptyCourse = false;
          } else {
            this.isEmptyCourse = true;
          }
        });
      this.requestClasses(classSetting)
        .then(() => {
          this.isClassFirstRequestCompile = true;
          if (this.classList.length !== 0) {
            this.isEmptyClass = false;
          } else {
            this.isEmptyClass = true;
          }
        });
    }
  }
</script>
