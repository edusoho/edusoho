<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div :key="swiperKey" :class="['swiper-container', moduleType]">
      <div class="swiper-wrapper">
        <template v-if="moduleData.length">
          <div
            v-for="(item, index) in moduleData"
            :key="index"
            class="swiper-slide"
          >
            <div class="swiper-slide-container">
              <img :src="item.image.uri">
            </div>
          </div>
        </template>

        <div v-else class="swiper-slide">
          <div class="swiper-slide-container">{{ 'carousel' | trans }}</div>
        </div>
      </div>
      <div class="pagination" />
    </div>
  </layout>
</template>

<script>
import Swiper from 'swiper/dist/idangerous.swiper.min.js';
import 'swiper/dist/idangerous.swiper.css';
import moduleMixin from '../moduleMixin';

export default {
  name: 'Swiper',

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
    this.initSwiepr();
  },

  methods: {
    initSwiepr() {
      new Swiper(`.${this.moduleType}`, {
        pagination : `.${this.moduleType} .pagination`,
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
  height: 136px;

  .swiper-slide {
    box-sizing: border-box;
    padding-right: 5px;
    padding-left: 5px;

    &-container {
      overflow: hidden;
      height: 100%;
      border-radius: 8px;
      background-color: #e1e1e1;
      font-size: 26px;
      color: #919191;
      font-weight: bold;
      text-align: center;
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
    left: 34px;
    bottom: 8px;
    z-index: 20;
    margin: 0;

    /deep/ .swiper-pagination-switch {
      display: inline-block;
      width: 12px;
      height: 2px;
      margin-right: 4px;
      background: rgba(255, 255, 255, 0.6);
      transform: matrix(-1, 0, 0, 1, 0, 0);
    }

    /deep/ .swiper-active-switch {
      background: #fff;
    }
  }
}
</style>
