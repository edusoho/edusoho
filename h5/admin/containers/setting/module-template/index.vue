<template>
  <div class="module-template" @click="activeModule">
    <!-- 轮播 -->
    <carousel v-if="module.type === moduleType.slideShow"
      :active="isActive" :moduleData="module"
      @updateModule="updateHandler(module, index)"></carousel>

    <!-- 课程列表 -->
    <course v-if="module.type === moduleType.courseList"
      :active="isActive" :moduleData="module"
      @updateModule="updateHandler(module, index)"></course>

    <!-- 广告海报 -->
    <poster v-if="module.type === moduleType.poster"
      :active="isActive" :moduleData="module"
      @updateModule="updateHandler(module, index)"></poster>

    <img class="icon-delete" src="static/images/delete.png" @click="handleRemove(module, index)" v-show="isActive">
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
    index: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {
        if (this.active) return;
        this.$emit('activeModule', this.index);
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
    updateHandler(data, index) {
      console.log(index, 'updateHandler')
      this.$emit('updateModule', data);
    },
    handleRemove(data, index) {
      this.$emit('removeModule', data);
    },
  }
}
</script>
