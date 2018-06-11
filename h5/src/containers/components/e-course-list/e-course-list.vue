<template>
  <div class="e-course-list">
    <div class="e-course-list__header">
      <div class="clearfix">
        <span class="e-course-list__list-title">{{ courseList.title }}</span>
        <span class="e-course-list__more">
          <!-- 修改为路由形式 -->
          <a class="more-text pull-left">更多</a>
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
        title: '更多课程',
        type: 'price'
      };
    },
  }
</script>
