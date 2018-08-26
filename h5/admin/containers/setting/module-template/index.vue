<template>
  <div>
    <carousel v-if="module.type === moduleType.slideShow" :active="active"></carousel>
    <course v-if="module.type === moduleType.courseList" :active="active"></course>
  </div>
</template>

<script>
import Carousel from '../carousel';
import Course from '../course';

export default {
  components: {
    'carousel': Carousel,
    'course': Course,
  },
  props: {
    module: {
      type: Object,
      default: {},
    },
    active: {
      type: Boolean,
      default: true,
    },
  },
  data () {
    return {
      moduleType: {
        slideShow: 'slide_show',
        courseList: 'course_list',
        poster: 'poster'
      }
    }
  }
}
</script>
