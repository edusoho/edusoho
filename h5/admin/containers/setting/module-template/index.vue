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
      type: Number,
      default: 0,
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
  watch: {
    saveFlag(value) {
      if (!value) return;
      this.triggerValidate();
    }
  },
  created() {
    // 每个模块唯一值
    this.module.moduleType = this.moduleKey;
    this.triggerValidate();
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
        poster: 'poster',
        incomplete: false,
      }
    }
  },
  methods: {
    activeModule() {
      this.isActive = true;
    },
    updateHandler() {
    },
    triggerValidate() {
      const incomplete = validate(this.module, this.saveFlag);
      this.$emit('updateModule', {
        incomplete,
        updateModule: this.module,
      });
    },
    handleRemove(data, index) {
      this.$emit('removeModule', data);
    },
  }
}
</script>
