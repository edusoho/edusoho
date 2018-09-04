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
      <e-course v-for="item in courseList.items" :key="item.id" :course="item" :type="type" :feedback="feedback">
      </e-course>
    </div>
  </div>
</template>

<script>
  import course from '../e-course/e-course';

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
      }
    },
    components: {
      'e-course': course,
    },
    data() {
      return {
        type: 'price',
      };
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
    },
    watch: {
      sort(value) {
        console.log('sort', value)
        this.fetchCourse();
      },
      limit(value, oldValue) {
        console.log('limit', value)
        if (oldValue > value) {
          const deleteIndex = value - oldValue
          this.courseList.items.splice(deleteIndex);
          return;
        }
        this.fetchCourse();
      },
      lastDays(value) {
        console.log('lastDays', value)
        this.fetchCourse();
      },
      categoryId(value) {
        console.log('categoryId', value)
        this.fetchCourse();
      },
      sourceType(value, oldValue) {
        console.log('sourceType', value, oldValue)
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

        const params = {
          sort: this.sort,
          limit: this.limit,
          lastDays: this.lastDays,
          categoryId: this.categoryId,
        };
        this.$emit('fetchCourse', {
          index: this.index,
          params,
        });
      }
    },
  }
</script>
