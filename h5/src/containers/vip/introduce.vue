<template>
  <div class="vip-introduce">
    <swiper :options="swiperOption">
      <swiper-slide v-for="(img, index) in bgImgs" :key="index">
        <img class="card-bg-img" :src="img">
      </swiper-slide>
    </swiper>
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