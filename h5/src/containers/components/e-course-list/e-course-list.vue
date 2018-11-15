<template>
  <div class="e-course-list">
    <div class="e-course-list__header">
      <div class="clearfix">
        <span class="e-course-list__list-title text-overflow">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <span class="more-text pull-left" @click="jumpTo(courseList.source)">更多</span>
        </span>
      </div>
    </div>
    <div class="e-course-list__body">
      <e-class v-for="item in courseList.items"
        :key="item.id"
        :course="item | courseListData(typeList)"
        :typeList="typeList"
        :feedback="feedback">
      </e-class>
    <!--   <e-course
        v-for="item in courseList.items"
        :key="item.id"
        :course="item"
        :type="type"
        :typeList="typeList"
        :feedback="feedback">
      </e-course> -->
    </div>
    <div v-show="courseItemData" class="e-course__empty">暂无课程</div>
  </div>
</template>

<script>
// import course from '../e-course/e-course';
import eClass from '../e-class/e-class';
import courseListData from '@admin/utils/filter-course.js';

  export default {
    props: {
      courseList: {
        type: Object,
        default: {},
      },
      feedback: {
        type: Boolean,
        default: true,
      },
      index: {
        type: Number,
        default: -1,
      },
      typeList: {
        type: String,
        default: 'course_list'
      }
    },
    components: {
      // 'e-course': course,
      'e-class': eClass,
    },
    data() {
      return {
        type: 'price'
      };
    },
    filters: {
      courseListData,
    },
    computed: {
      sourceType: {
        get() {
          return this.courseList.sourceType;
        },
      },
      sort: {
        get() {
          return this.courseList.sort;
        },
      },
      lastDays: {
        get() {
          return this.courseList.lastDays;
        },
      },
      limit: {
        get() {
          return this.courseList.limit;
        },
      },
      categoryId: {
        get() {
          return this.courseList.categoryId;
        },
      },
      courseItemData: {
        get() {
          return !this.courseList.items.length ? true : false;
        },
        set() {}
      },
    },
    watch: {
      sort(value) {
        this.fetchCourse();
      },
      limit(value, oldValue) {
        if (oldValue > value) {
          const newItems = this.courseList.items.slice(0, value);
          this.courseList.items = newItems;
          return;
        }
        this.fetchCourse();
      },
      lastDays(value) {
        this.fetchCourse();
      },
      categoryId(value) {
        this.fetchCourse();
      },
      sourceType(value, oldValue) {
        if (value !== oldValue) {
          this.courseList.items = [];
        }
        this.fetchCourse();
      },
    },
    created() {
      this.fetchCourse();
    },
    methods: {
      jumpTo(source) {
        if (!this.feedback) {
          return;
        }
        this.$router.push({
          name: 'more',
          query: {...this.source}
        });
      },
      fetchCourse() {
        if (this.sourceType === 'custom') return;

        let params = {
          sort: this.sort,
          limit: this.limit,
          lastDays: this.lastDays,
          categoryId: this.categoryId,
        };

        if (this.typeList === 'class_list') {
          params = {
            sort: 'recommendedSeq',
            limit: this.limit,
            lastDays: 'studentNum',
            categoryId: this.categoryId,
          };
        }

        this.$emit('fetchCourse', {
          index: this.index,
          params,
        });
      }
    },
  }
</script>
