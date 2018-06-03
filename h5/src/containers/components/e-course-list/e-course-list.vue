<template>
  <div class="e-course-list">
    <div class="e-course-list__header">
      <div class="clearfix">
        <span class="e-course-list__list-title">推荐课程</span>
        <span class="e-course-list__more">
          <a class="more-text pull-left">更多</a>
        </span>
      </div>
    </div>
    <div class="e-course-list__body">
      <e-course></e-course>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import course from '../e-course/e-course';

  export default {
    created() {

    },
    data () {
      return {

      };
    },
    components: {
      'e-course': course,
    }
  }
</script>
