<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad"
  >
    <courseItem v-for="course in courseList"
      :type="courseItemType"
      :course="course"
    ></courseItem>
  </van-list>
</template>

<script>
  import courseItem from '../e-course/e-course.vue';

  export default {
    components: {
      courseItem,
    },
    props: {
      courseList: Array,
    },
    data() {
      return {
        list: [],
        loading: false,
        finished: false,
        courseItemType: 'rank'
      };
    },

    methods: {
      onLoad() {

      }
    }
  }
</script>
