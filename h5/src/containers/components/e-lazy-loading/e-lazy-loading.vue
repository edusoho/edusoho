<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad">
    <courseItem v-for="(course, index) in courseList"
      :key="index"
      :type="courseItemType"
      :typeList="typeList"
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

    props: {
      courseList: Array,
      isRequestCompile: Boolean,
      isAllData: Boolean,
      courseItemType: String,
      typeList: {
        type: String,
        default: 'course_list'
      }
    },

    data() {
      return {
        list: [],
        finished: false
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
      isAllData() {
        this.loading = false;
        this.finished = this.isAllData;
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
