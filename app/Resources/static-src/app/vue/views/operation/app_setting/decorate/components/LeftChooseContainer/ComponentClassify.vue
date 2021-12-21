<template>
  <div class="component-classify-item">
    <div :class="{ active: isActive }" @click="changeClassify">
      <svg-icon :icon="classify.icon" />
      <div class="classify-title">{{ classify.title }}</div>
    </div>
    <template v-if="classify.components">
      <div v-show="isActive" class="add-component-lists">
        <div class="clearfix">
          <component-classify-item
            v-for="component in classify.components"
            :key="component.type"
            :component="component"
            :coupon-enabled="couponEnabled"
            :vip-enabled="vipEnabled"
            @click.native="addModule(component.type)"
          />
        </div>
        <div class="arrow-content" :style="{ top: (88 * index + 44) + 'px' }"></div>
      </div>
    </template>
  </div>
</template>

<script>
import ComponentClassifyItem from './ComponentClassifyItem.vue';

export default {
  props: {
    classify: {
      type: Object,
      required: true
    },

    index: {
      type: Number,
      required: true
    },

    currentClassify: {
      type: Number,
      required: true
    },

    vipEnabled: {
      type: Boolean,
      default: false
    },

    couponEnabled: {
      type: Boolean,
      default: false
    }
  },

  components: {
    ComponentClassifyItem
  },

  computed: {
    isActive() {
      return this.index === this.currentClassify;
    }
  },

  methods: {
    addModule(type) {
      this.$emit('add-module', type);
    },

    changeClassify() {
      this.$emit('change-classify', this.index);
    }
  }
}
</script>

<style lang="less" scoped>
.component-classify-item {
  margin-bottom: 40px;
  text-align: center;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.35);

  .svg-icon {
    font-size: 24px;
    display: block;
    margin: 8px auto;
  }

  .classify-title {
    margin-top: 4px;
    line-height: 16px;
    height: 16px;
    font-size: 12px;
  }

  .active {
    .svg-icon {
      color: #fff;
    }
    .classify-title {
      color: #fff;
    }
  }

  .add-component-lists {
    position: absolute;
    top: 0;
    left: 80px;
    width: 190px;
    height: 100%;
    background: #fff;
    box-shadow: 4px 0px 8px 0px rgba(0, 0, 0, 0.05);
    z-index: 10;
    padding: 11px;
    color: #333;

    .arrow-content {
      position: absolute;
      width: 8px;
      height: 8px;
      left: 0;
      margin-left: -4px;
      transform: rotate(45deg);
      background-color: #fff;
    }
  }
}
</style>
