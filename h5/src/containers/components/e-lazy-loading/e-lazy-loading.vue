<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad"
  >
    <courseItem v-for="(course, index) in courseList"
      :key="index"
      :type="courseItemType"
      :course="course | courseListData(courseItemType, typeList)"
    ></courseItem>
  </van-list>
</template>

<script>
  import courseItem from '../e-class/e-class.vue';
  import courseListData from '../../../utils/filter-course.js';

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
      },
      typeList: {
        type: String,
        default: 'course_list'
      }
    },

    data() {
      return {
        list: [],
        finished: false,
      };
    },

    filters: {
      courseListData,
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
