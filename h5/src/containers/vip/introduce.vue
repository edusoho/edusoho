<template>
  <div class="vip-introduce gray-border-bottom">
    <swiper :options="swiperOption">
      <swiper-slide v-for="(item, index) in levels" :key="index">
        <img class="card-bg-img" :src="item.background">
        <div class="vip-info">
          <div class="text-18">{{item.name}}</div>
          <div class="text-12 vip-rights-num">20门课程 6个班级</div>
          <div class="text-12">10元 / 月</div>
        </div>
      </swiper-slide>
    </swiper>
    <div class="vip-introduce__text">
      <header class="title-18 text-center mb20">{{levels[activeIndex].name}}介绍</header>
      <div class="text-content">{{levels[activeIndex].description}}</div>
    </div>
    <div class="text-center mtl"><div class="btn-join-vip">开通会员</div></div>
  </div>
</template>

<script>
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import 'swiper/dist/css/swiper.css';

  export default {
    components: {
      swiper,
      swiperSlide
    },
    props:{
      levels: Array
    },
    data() {
      const that = this;
      return {
        activeIndex: 0,
        swiperOption: {
          notNextTick: true,
          loop: false,
          centeredSlides: true,
          spaceBetween: 20,
          slidesPerView: 1.5,
          observer: true,
          observeParents: true,
          on: {
            slideChangeTransitionStart: function() {
              that.activeIndex = this.activeIndex;
              that.$emit('activeIndex', this.activeIndex);
            }
          }
        }
      }
    },
    methods: {
    }
  }

</script>