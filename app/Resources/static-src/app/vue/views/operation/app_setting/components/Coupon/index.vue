<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="coupon-title" v-if="moduleData.titleShow === 'show'">优惠券</div>

    <div :class="['coupon-container', moduleType]">
      <div v-if="moduleData.items.length > 1" class="swiper-wrapper clearfix">
        <div
          v-for="item in moduleData.items"
          :key="item.id"
          class="swiper-slide"
        >
          <div class="swiper-slide-container">
            <coupon-item :is-more="true" :coupon="item" />
          </div>
        </div>
      </div>

      <template v-else-if="moduleData.items.length > 0">
        <coupon-item v-for="item in moduleData.items" :key="item.id" :coupon="item" />
      </template>

      <div v-else class="empty">优惠卷</div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import Swiper from 'swiper/dist/idangerous.swiper.min.js';
import 'swiper/dist/idangerous.swiper.css';
import moduleMixin from '../moduleMixin';
import CouponItem from './Item.vue';

export default {
  name: 'Coupon',

  mixins: [moduleMixin],

  components: {
    CouponItem
  },

  data() {
    return {
      swiperKey: 0
    }
  },

  watch: {
    moduleData: {
      handler: function() {
        this.swiperKey++;
        this.reInitSwiper();
      },
      deep: true
    }
  },

  mounted() {
    this.initSwiepr();
  },

  methods: {
    initSwiepr() {
      if (_.size(this.moduleData.items) <= 1) return;

      new Swiper(`.${this.moduleType}`, {
        slidesPerView: 1.5
      });
    },

    reInitSwiper() {
      this.$nextTick(() => {
        this.initSwiepr();
      });
    }
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
  height: 112px;

  .empty {
    width: 100%;
    height: 100%;
    background-color: #e1e1e1;
    font-size: 26px;
    color: #919191;
    font-weight: bold;
  }

  .swiper-slide-container {
    position: relative;
    margin-right: 24px;
    height: 100%;
  }
}
</style>
