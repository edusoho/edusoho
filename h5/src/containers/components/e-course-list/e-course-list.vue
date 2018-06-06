<template>
  <div class="e-course-list">
    <div class="e-course-list__header">
      <div class="clearfix">
        <span class="e-course-list__list-title">{{ title }}</span>
        <span class="e-course-list__more">
          <a class="more-text pull-left">更多</a>
        </span>
      </div>
    </div>
    <div class="e-course-list__body">
      <e-course v-for="course in courses" :key="course.id" :course="course"></e-course>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import course from '../e-course/e-course';
  const url = '/api/plugins/we_chat_app/course';
  import Api from '@/api';

  export default {
    created() {
      Api.course()
        .then((data) => {
          this.courses = data.data;
          console.log(data, 'success');
        })
    },
    data() {
      return {
        courses: [],
        title: '更多课程'
      };
    },
    components: {
      'e-course': course,
    }
  }
</script>
