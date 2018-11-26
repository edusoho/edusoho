<template>
  <div class="e-learn">
    <van-tabs
      v-model="active"
      class="after-tabs">
      <van-tab v-for="item in tabs"
        :title="item" :key="item"></van-tab>
    </van-tabs>
    <emptyCourse v-if="(isEmptyCourse || isEmptyClass) && isFirstRequestCompile" :type="typeList"></emptyCourse>
    <div v-else>
      <lazyLoading
        v-show="active==0"
        :courseList="courseList"
        :isAllCourse="isAllCourse"
        :courseItemType="courseItemType"
        v-model="isCourseComplete"
        @needRequest="sendRequest"
        :typeList="'course_list'"
      ></lazyLoading>
      <lazyLoading
        v-show="active==1"
        :courseList="classList"
        :isAllCourse="isAllCourse"
        :courseItemType="courseItemType"
        v-model="isClassComplete"
        @needRequest="sendRequest"
        :typeList="'class_list'"
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
        isEmptyCourse: true,
        isEmptyClass: true,
        isFirstRequestCompile: false,
        isCourseComplete: false,
        isClassComplete: false,
        isAllCourse: false,
        courseList: [],
        classList: [],
        isAllClass: false,
        offset: 0,
        limit: 10,
        active: 0,
        tabs: ['我的课程', '我的班级']
      };
    },
    computed: {
      typeList() {
        return this.active == 0 ? 'course_list' : 'class_list';
      }
    },
    methods: {
      judgeIsAllCourse(courseInfomation) {
        if (this.courseList.length == courseInfomation.paging.total) {
          return true
        }
        return false
      },

      judgeIsAllClass(courseInfomation) {
        if (this.classList.length == courseInfomation.paging.total) {
          return true
        }
        return false
      },

      requestCourses(setting) {
        this.isCourseComplete = false;
        return Api.myStudyCourses({
          params: setting
        }).then((data) => {
          let isAllCourse;
          isAllCourse = this.judgeIsAllCourse(data);
          if (!isAllCourse) {
            data.data.forEach(element => {
              this.courseList.push(element);
              this.offset++;
            })
          }
          this.isAllCourse = isAllCourse;
          this.isCourseComplete = true;
        }).catch((err) => {
          console.log(err, 'error');
        })
      },

      requestClasses(setting) {
        this.isClassComplete = false;
        return Api.myStudyClasses({
          params: setting
        }).then((data) => {
          let isAllClass;
          isAllClass = this.judgeIsAllClass(data);
          if (!isAllClass) {
            data.data.forEach(element => {
              this.classList.push(element);
              this.offset++;
            })
          }
          this.isAllClass = isAllClass;
          this.isClassComplete = true;
        }).catch((err) => {
          console.log(err, 'error');
        })
      },
      sendRequest() {
        const args = {
          offset: this.offset,
          limit: this.limit
        };

        if (!this.isAllCourse) this.requestCourses(args);
        if (!this.isAllClass) this.requestClasses(args);
      }
    },

    created() {
      const setting = {
            offset: this.offset,
            limit: this.limit
          };
      this.requestCourses(setting)
        .then(() => {
          this.isFirstRequestCompile = true;
          if (this.courseList.length !== 0) {
            this.isEmptyCourse = false;
          } else {
            this.isEmptyCourse = true;
          }
        });
      this.requestClasses(setting)
        .then(() => {
          this.isFirstRequestCompile = true;
          if (this.classList.length !== 0) {
            this.isEmptyClass = false;
          } else {
            this.isEmptyClass = true;
          }
        });
    }

  }
</script>
