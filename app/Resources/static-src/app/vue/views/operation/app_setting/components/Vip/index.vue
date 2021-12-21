<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="vip-title" v-if="moduleData.titleShow === 'show'">会员专区</div>
    <div :class="['vip-container', moduleType]">
      <div class="swiper-wrapper clearfix">
        <div
          v-for="(item, index) in moduleData.items"
          :key="index"
          class="swiper-slide"
        >
          <div class="swiper-slide-container">
            <div class="vip-info">
              <div class="vip-info__name text-overflow">{{ item.name }}</div>
              <div class="vip-info__free">{{ item.freeCourseNum }} 门课程，{{ item.freeClassroomNum }} 门班级</div>
            </div>
            <img :src="item.background">
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import Swiper from 'swiper/dist/idangerous.swiper.min.js';
import 'swiper/dist/idangerous.swiper.css';
import moduleMixin from '../moduleMixin';

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
    this.initSwiepr();
  },

  methods: {
    initSwiepr() {
      new Swiper(`.${this.moduleType}`, {
        slidesPerView: 1.8
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
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 500;
  color: #333;
  line-height: 24px;
}

.vip-container {
  overflow: hidden;
  padding-right: 16px;
  padding-left: 16px;
  width: 100%;
  height: 104px;

  .swiper-slide-container {
    position: relative;
    margin-right: 24px;
    height: 100%;

    .vip-info {
      position: absolute;
      top: 50%;
      left: 16px;
      transform: translateY(-50%);
      color: #fff;

      &__name {
        font-weight: bold;
      }

      &__free {
        font-size: 12px;
      }
    }

    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
