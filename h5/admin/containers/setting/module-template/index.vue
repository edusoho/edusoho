<template>
  <div @click="activeModule">
    <carousel v-if="module.type === moduleType.slideShow"
              :active="isActive"
              @updateModule="updateHandler"></carousel>
    <course v-if="module.type === moduleType.courseList"
            :active="isActive"
            :moduleData="module"
            @updateModule="updateHandler"></course>
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
    },
    updateHandler(data) {
      this.$emit('updateModule', data);
    }
  }
}
</script>
