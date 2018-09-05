<template>
  <div class="module-template" @click="activeModule">
    <!-- 轮播 -->
    <carousel v-if="module.type === moduleType.slideShow"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></carousel>

    <!-- 课程列表 -->
    <course v-if="module.type === moduleType.courseList"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></course>

    <!-- 广告海报 -->
    <poster v-if="module.type === moduleType.poster"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></poster>

    <img class="icon-delete" src="static/images/delete.png" @click="handleRemove(module, index)" v-show="isActive">
  </div>
</template>

<script>
import Carousel from '../carousel';
import Course from '../course';
import Poster from '../poster';
import validate from '@admin/utils/module-validator';

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
    moduleKey: {
      type: String,
      default: 'demo-1',
    },
    saveFlag: {
      type: Boolean,
      default: false,
    }
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
    },
    validateFuc() {
      if (this.saveFlag) {
        return validate(this.module);
      }
    },
  },
  created() {
    // 每个模块唯一值
    this.module.moduleType = this.moduleKey;
  },
  updated() {
    // 每个模块唯一值
    this.module.moduleType = this.moduleKey;
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
      let incompleteBoolean = true;
      if (this.saveFlag) {
        incompleteBoolean = validate(this.module);
      }
      this.$emit('updateModule', {
        updateModule: data,
        incomplete: incompleteBoolean
      });
    },
    handleRemove(data, index) {
      this.$emit('removeModule', data);
    },
  }
}
</script>
