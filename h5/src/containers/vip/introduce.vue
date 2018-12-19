<template>
  <div class="vip-introduce gray-border-bottom">
    <swiper :options="swiperOption">
      <swiper-slide v-for="(img, index) in bgImgs" :key="index">
        <img class="card-bg-img" :src="img">
      </swiper-slide>
    </swiper>
    <div class="vip-introduce__text">
      <header class="title-18 text-center mb20">会员介绍</header>
      <div class="text-content">白金会员，具体包含了20门课程和6个班级。可以帮助您开展更好的系统化学习。</div>
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
    data() {
      return {
        bgImgs: [
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png',
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png',
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png'
        ],
        activeIndex: 1,
        swiperOption: {
          notNextTick: true,
          loop: true,
          centeredSlides: true,
          loopAdditionalSlides: 1,
          spaceBetween: 20,
          slidesPerView: 1.5,
          observer: true,
          observeParents: true,
        }
      }
    },
    methods: {
      onChange(index) {
        this.activeIndex = index + 1;
        console.log(222,index)
      }
    }
  }

</script>