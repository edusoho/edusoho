<template>
  <div @click="activeModule">
    <carousel v-if="module.type === moduleType.slideShow"
              :active="isActive"></carousel>
    <course v-if="module.type === moduleType.courseList"
            :active="isActive"
            :moduleData="module"></course>
    <poster v-if="module.type === moduleType.poster"
            :active="isActive"
            :moduleData="module"></poster>
  </div>
</template>

<script>
import Carousel from '../carousel';
import Course from '../course';
import Poster from '../poster';

export default {
  components: {
    'carousel': Carousel,
    'course': Course,
    'poster': Poster
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
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {
        this.$emit('activeModule', {
          moduleId: this.module.moduleType
        });
      }
    }
  },
  data () {
    return {
      moduleType: {
        slideShow: 'slide_show',
        courseList: 'course_list',
        poster: 'poster'
      }
    }
  },
  methods: {
    activeModule() {
      this.isActive = true;
    }
  }
}
</script>
