<template>
  <van-list
    v-model="loading"
    :finished="finished"
    @load="onLoad">
    <courseItem v-for="(course, index) in courseList"
      :key="index"
      :type="courseItemType"
      :typeList="typeList"
      :course="course | courseListData(listObj)"
    ></courseItem>
  </van-list>
</template>

<script>
  import courseItem from '../e-class/e-class.vue';
  import courseListData from '../../../utils/filter-course.js';
  import { mapState } from 'vuex';

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
        default: 'course'
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
      ...mapState(['courseSettings']),
      loading: {
        get() {
          return !this.isRequestCompile;
        },
        set(v) {
          console.log(v, 'value');
        }
      },
      listObj() {
        return {
          type: this.courseItemType,
          typeList: this.typeList,
          showStudent: this.courseSettings ?
                      Number(this.courseSettings.show_student_num_enabled) : true,
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
