<template>
  <div class="e-learn">
    <emptyCourse v-if="isEmptyCourse && isFirstRequestCompile"></emptyCourse>
    <lazyLoading v-else
      :courseList = courseList
    ></lazyLoading>
  </div>
</template>
<script>
  import emptyCourse from './emptyCourse/emptyCourse.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';
  import store from '@/store';
  import Api from '@/api';

  export default {
    components: {
      emptyCourse,
      lazyLoading,
    },
    data() {
      return {
        isEmptyCourse: true,
        isFirstRequestCompile: false,
        courseList: []
      };
    },
    methods: {
      judgeIsEmptyCourse(courseInfomation) {
        if (courseInfomation.data.length !== 0) {
          return false
        }
        return true
      }
    },
    beforeRouteEnter(to, from, next) {
      // 判断是否登录
      const isLogin = !!store.state.token;

      !isLogin ? next({name: 'prelogin',query: { redirect: to.name }}) : next();
    },
    created() {
      Api.myStudyState().then((data) => {
        console.log(data, 'my study');
        const isEmptyCourse = this.judgeIsEmptyCourse(data);
        this.isEmptyCourse = isEmptyCourse;
        this.isFirstRequestCompile = true;
        if (!isEmptyCourse) this.courseList = data.data;
      }).catch((err) => {
        console.log(err, 'error');
      });;
    }

  }
</script>
