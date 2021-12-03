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
.swiper-container {
  position: relative;
  width: 100%;
  height: 150px;

  .swiper-slide {
    box-sizing: border-box;
    padding-right: 5px;
    padding-left: 5px;

    &-container {
      height: 100%;
      border-radius: 8px;
      background-color: #e1e1e1;
      font-size: 26px;
      color: #919191;
      font-weight: bold;
      text-align: center;
      line-height: 140px;
      transform: scale(0.96);
      transition: all 0.3s ease;

      img {
        width: 100%;
        height: 100%;
      }
    }

    &-active {
      .swiper-slide-container {
        transform: scale(1);
      }
    }
  }

  .pagination {
    position: absolute;
    z-index: 20;
    margin: 0;
    bottom: 10px;
    width: 100%;
    text-align: center;

    /deep/ .swiper-pagination-switch {
      display: inline-block;
      width: 6px;
      height: 6px;
      background: rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      margin: 0 4px;
    }

    /deep/ .swiper-active-switch {
      width: 15px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 8px;
    }
  }
}
</style>
