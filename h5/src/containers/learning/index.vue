<template>
  <div class="e-learn">
    <emptyCourse v-if="isEmptyCourse && isFirstRequestCompile"></emptyCourse>
    <lazyLoading v-else
      :courseList="courseList"
      :isAllCourse="isAllCourse"
      :courseItemType="courseItemType"
      v-model="isRequestCompile"
      @needRequest="sendRequest"
    ></lazyLoading>
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
        isFirstRequestCompile: false,
        isRequestCompile: false,
        isAllCourse: false,
        courseList: [],
        offset: 0,
        limit: 10,
      };
    },
    methods: {
      judegIsAllCourse(courseInfomation) {
        if (this.courseList.length == courseInfomation.paging.total) {
          return true
        }
        return false
      },

      requestCourses(setting) {
        this.isRequestCompile = false;
        return Api.myStudyState({
          params: setting
        }).then((data) => {
          let isAllCourse;
          isAllCourse = this.judegIsAllCourse(data);
          if (!isAllCourse) {
            data.data.forEach(element => {
              this.courseList.push(element);
              this.offset++;
            })
          }
          this.isAllCourse = isAllCourse;
          this.isRequestCompile = true;
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
    }

  }
</script>
