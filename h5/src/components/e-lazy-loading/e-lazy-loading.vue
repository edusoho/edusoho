<template>
  <van-list v-model="loading" :finished="finished" @load="onLoad">
    <template v-if="typeList === 'item_bank_exercise'">
      <courseRow
        v-for="(course, index) in courseList"
        :key="index"
        :type="courseItemType"
        :normal-tag-show="normalTagShow"
        :vip-tag-show="vipTagShow"
        :type-list="typeList"
        :is-vip="course.vipLevelId"
        :is-app-use="false"
        :discountType="discountType"
        :discount="discount"
        :course-type="courseType"
        :course="course | courseListData(listObj, 'new', 'h5')"
      />
    </template>
    <courseItem
      v-else
      v-for="(course, index) in courseList"
      :key="index"
      :type="courseItemType"
      :normal-tag-show="normalTagShow"
      :vip-tag-show="vipTagShow"
      :type-list="typeList"
      :is-vip="course.vipLevelId"
      :discountType="discountType(course)"
      :discount="discount(course)"
      :course-type="courseType(course)"
      :course="course | courseListData(listObj)"
    />
  </van-list>
</template>

<script>
import courseRow from '../e-row-class/e-row-class.vue';
import courseItem from '../e-class/e-class.vue';
import courseListData from '@/utils/filter-course.js';
import { mapState } from 'vuex';

export default {
  components: {
    courseItem,
    courseRow,
  },

  filters: {
    courseListData,
  },

  props: {
    courseList: Array,
    isRequestCompile: Boolean,
    isAllData: Boolean,
    courseItemType: String,
    typeList: {
      type: String,
      default: 'course_list',
    },
    normalTagShow: {
      type: Boolean,
      default: true,
    },
    vipTagShow: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      list: [],
      finished: false,
    };
  },

  computed: {
    ...mapState(['courseSettings']),
    loading: {
      get() {
        return !this.isRequestCompile;
      },
      set(v) {
        console.log(v, 'value');
      },
    },
    listObj() {
      return {
        type: this.courseItemType,
        typeList: this.typeList,
        showStudent: this.courseSettings
          ? Number(this.courseSettings.show_student_num_enabled)
          : true,
      };
    },
  },

  watch: {
    isAllData() {
      this.loading = false;
      this.finished = this.isAllData;
    },
  },

  methods: {
    onLoad() {
      // 通知父组件请求数据并更新courseList
      if (this.isRequestCompile) this.$emit('needRequest');
    },
    discountType(course) {
      if (this.typeList === 'course_list') {
        return course.courseSet.discountType;
      }
      return '';
    },
    discount(course) {
      if (this.typeList === 'course_list') {
        return course.courseSet.discount;
      }
      return '';
    },
    courseType(course) {
      if (this.typeList === 'course_list') {
        return course.courseSet.type;
      }
      return '';
    },
  },
};
</script>
