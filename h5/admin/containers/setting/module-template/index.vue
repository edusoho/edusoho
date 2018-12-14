<template>
  <div class="module-template" @click="activeModule">
    <!-- 基础组件——轮播 -->
    <carousel v-if="module.type === moduleDefault.slideShow.type"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></carousel>

    <!-- 基础组件——课程列表 -->
    <course v-if="module.type === moduleDefault.courseList.type" :key="1"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></course>

    <!-- 班级列表 -->
    <course v-if="module.type === moduleDefault.classList.type" :key="2"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></course>

    <!-- 广告海报 -->
    <poster v-if="module.type === moduleDefault.poster.type"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></poster>

    <!-- 优惠券 -->
    <coupon v-if="module.type === moduleDefault.coupon.type"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></coupon>

    <!-- 营销组件——拼团活动 -->
    <marketing-groupon v-if="module.type === moduleDefault.groupon.type"
      :active="isActive" :moduleData="module" :incomplete="validateFuc"
      @updateModule="updateHandler(module, index)"></marketing-groupon>

    <img class="icon-delete" src="static/images/delete.png" @click="handleRemove(module, index)" v-show="isActive">
  </div>
</template>

<script>
import Carousel from '../carousel';
import Course from '../course';
import Poster from '../poster';
import Coupon from '../coupon';
import MarketingGroupon from '../marketing-groupon';
import validate from '@admin/utils/module-validator';
import { MODULE_DEFAULT } from '@admin/config/module-default-config';

export default {
  components: {
    'carousel': Carousel,
    'course': Course,
    'poster': Poster,
    'marketing-groupon': MarketingGroupon,
    'coupon': Coupon
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
      incomplete: false,
      moduleDefault: MODULE_DEFAULT,
    }
  },
  methods: {
    activeModule() {
      this.isActive = true;
    },
    updateHandler() {
    },
    triggerValidate() {
      if (this.module.type === 'poster') {
        const linkData = this.module.data.link;
        if (linkData.type === 'url') {
          linkData.target = null;
        } else {
          linkData.url = '';
        }
      }
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
