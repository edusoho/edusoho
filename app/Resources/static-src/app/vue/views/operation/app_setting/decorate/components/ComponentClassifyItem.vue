<template>
  <div
    class="add-list-item pull-left"
    :class="getClassName"
  >
    <svg-icon :icon="component.icon" />
    <div class="component-title">{{ component.title }}</div>
  </div>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    component: {
      type: Object,
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

  computed: {
    getClassName() {
      if (!_.includes(['vip', 'coupon'], this.component.type)) {
        return '';
      }

      if (!this.couponEnabled || !this.vipEnabled) {
        return 'limit-add';
      }

      return '';
    }
  }
}
</script>

<style lang="less" scoped>
.add-list-item {
  position: relative;
  width: 78px;
  height: 72px;
  text-align: center;
  border-radius: 2px;
  cursor: pointer;
  margin-left: 10px;
  transition: all 0.3s ease;

  .svg-icon {
    font-size: 24px;
    display: block;
    margin: 8px auto;
  }

  .component-title {
    color: #4a4a4a;
    line-height: 16px;
    height: 16px;
    font-size: 12px;
  }

  &.limit-add {
    opacity: 0.3;
    cursor: not-allowed;
  }

  &:nth-of-type(even) {
    margin-left: 0;
  }

  &:hover {
    background: #31a1ff;
    color: #fff;
  }
}
</style>
