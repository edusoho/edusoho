<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad"
  >
    <courseItem v-for="(course, index) in courseList"
      :key="index"
      :type="courseItemType"
      :course="trans(course)"
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
      courseItemType: String,
      isMorePage: {
        type: Boolean,
        default: false
      }
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
      },
      trans(value) {
        if (this.isMorePage) {
          const course = Object.assign({}, value);
          course.courseSet = {
            title: course.title,
            id: course.defaultCourseID,
            cover: course.cover,
            studentNum: course.studentNum
          };
          course.price = course.minCoursePrice
          return course;
        }
        return value;
      }
    }
  }
</script>
