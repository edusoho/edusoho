<template>
  <div class="e-course">
    <a class="clearfix" href="">
      <div class="e-course__left pull-left">
        <img :src="course.courseSet.cover.large">
      </div>
      <div class="e-course__right pull-left">
        <div class="e-course__title text-overflow">{{ course.courseSetTitle || course.courseSet.subtitle }}</div>
        <div class="e-course__project text-overflow">
          <span v-if="course.title === '默认教学课程'">{{ course.title }}</span>
        </div>
        <switchBox :course="course" :type="type"></switchBox>
      </div>
    </a>
  </div>
</template>

<script>
  import switchBox from '../e-course-switch-box/e-course-switch-box.vue';

  export default {
    props: {
      course: {
        type: Object,
        default: {},
      },
      type: {
        type: String,
        default: 'price'
      }
    },
    data () {
      return {
      };
    },
    components: {
      switchBox,
    }
  }
</script>
