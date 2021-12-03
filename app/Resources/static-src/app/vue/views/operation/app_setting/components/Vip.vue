<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    <div class="vip-title">会员专区</div>
    <!-- <div :class="['vip-container', moduleType]">
      <div class="swiper-wrapper">
        <div
          v-for="(item, index) in moduleData"
          :key="index"
          class="swiper-slide"
        >
          <div class="swiper-slide-container">
            <img :src="item.image.url">
          </div>
        </div>
      </div>
    </div> -->
  </layout>
</template>

<script>
import Swiper from 'swiper/dist/idangerous.swiper.min.js';
import 'swiper/dist/idangerous.swiper.css';
import moduleMixin from './moduleMixin';

export default {
  name: 'Vip',

  mixins: [moduleMixin],

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
    // this.initSwiepr();
  },

  methods: {
    initSwiepr() {
      new Swiper(`.${this.moduleType}`, {
        autoplay: 5000,
        loop: true,
        slidesPerView: 1.1,
        centeredSlides: true
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
.vip-title {
  padding-right: 16px;
  padding-left: 16px;
  font-size: 16px;
  font-weight: 500;
  color: #333;
  line-height: 24px;
}
</style>
