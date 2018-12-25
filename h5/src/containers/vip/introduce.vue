<template>
  <div class="vip-introduce gray-border-bottom">
    <swiper :options="swiperOption">
      <swiper-slide v-for="(item, index) in levels" :key="index">
        <img class="card-bg-img" :src="item.background">
        <div class="vip-info">
          <div class="vip-info__name text-overflow">{{item.name}}</div>
          <div class="text-10 vip-rights-num">{{item.courses.data.length}}门课程 {{item.classrooms.data.length}}个班级</div>
          <div class="text-10">
            {{buyType === 'year' ? `${item.yearPrice}元 / 年` : `${item.monthPrice}元 / 月`}}
          </div>
        </div>
      </swiper-slide>
    </swiper>
    <div class="vip-introduce__text">
      <header class="title-18 text-center">{{levels[activeIndex].name}}介绍</header>
      <div class="text-content mt20">{{levels[activeIndex].description || '暂无介绍'}}</div>
    </div>
    <div class="text-center" v-if="!isVip || !user"><div class="btn-join-vip" @click="vipPopShow">开通会员</div></div>
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
      levels: {
        type: Array,
        default: () => {
          return [];
        },
      },
      isVip: {
        type: Object,
        default: () => {
          return {};
        },
      },
      buyType: {
        type: String,
        default: 'month'
      },
      enterIndex: {
        type: Number,
        default: 0
      },
      user: {
        type: Object,
        default: () => {
          return {};
        }
      }
    },
    data() {
      const that = this;
      return {
        activeIndex: 0,
        swiperOption: {
          notNextTick: true,
          loop: false,
          realIndex: 3,
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
      vipPopShow() {
        this.$emit('vipOpen', true);
      }
    }
  }

</script>
