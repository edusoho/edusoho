<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad"
  >
    <courseItem v-for="(course, index) in courseList"
      :key="index"
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

    model: {
      prop: 'isRequestCompile',
      event: 'needRequest'
    },

    props: {
      courseList: Array,
      isRequestCompile: Boolean,
      isAllCourse: Boolean,
      courseItemType: String
    },

    data() {
      return {
        list: [],
        finished: false,
      };
    },

    computed: {
      loading: {
        get() {
          return !this.isRequestCompile;
        },
        set(v) {
          console.log(v, 'value');
        }
      }
    },

    watch: {
      isAllCourse() {
        this.loading = false;
        this.finished = this.isAllCourse;
      }
    },

    methods: {
      onLoad() {
        // 通知父组件请求数据并更新courseList
        if (this.isRequestCompile) this.$emit('needRequest');
      }
    }
  }
</script>
