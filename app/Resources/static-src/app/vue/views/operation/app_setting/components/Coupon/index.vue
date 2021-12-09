<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    <div class="coupon-title" v-if="moduleData.titleShow === 'show'">优惠券</div>

    <div class="coupon-container">
      <div v-if="moduleData.items.length > 1">
        轮播图
      </div>

      <template v-else-if="moduleData.items.length > 0">
        <coupon-item />
      </template>

      <div v-else class="empty">暂未添加优惠卷</div>
    </div>
  </layout>
</template>

<script>
import moduleMixin from '../moduleMixin';
import CouponItem from './Item.vue';

export default {
  name: 'Coupon',

  mixins: [moduleMixin],

  components: {
    CouponItem
  }
}
</script>

<style lang="less" scoped>
.coupon-title {
  padding-right: 16px;
  padding-left: 16px;
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 500;
  color: #333;
  line-height: 24px;
}

.coupon-container {
  overflow: hidden;
  padding-right: 16px;
  padding-left: 16px;
  width: 100%;
  height: 104px;

  .empty {
    width: 100%;
    height: 100%;
    background-color: #eee;
  }
}
</style>
