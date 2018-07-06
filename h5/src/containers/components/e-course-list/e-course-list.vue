<template>
  <div class="e-course-list">
    <div class="e-course-list__header">
      <div class="clearfix">
        <span class="e-course-list__list-title">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <span class="more-text pull-left" @click="jumpTo(courseList.source)">更多</span>
        </span>
      </div>
    </div>
    <div class="e-course-list__body">
      <e-course v-for="item in courseList.items" :key="item.id" :course="item" :type="type"></e-course>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import course from '../e-course/e-course';
  const url = '/api/plugins/we_chat_app/course';

  export default {
    props: {
      courseList: {
        type: Object,
        default: {}
      }
    },
    components: {
      'e-course': course,
    },
    data() {
      return {
        type: 'price',
        source: this.courseList.source
      };
    },
    methods: {
      jumpTo(source) {
        this.$router.push({
          path: '/more',
          query: {...this.source}
        });
      }
    }
  }
</script>
