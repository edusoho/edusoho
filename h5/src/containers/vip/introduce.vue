<template>
  <div class="vip-introduce gray-border-bottom">
    <swiper :options="swiperOption" ref="mySwiper">
      <swiper-slide v-for="(item, index) in levels" :key="index">
        <img class="card-bg-img" :src="item.background">
        <div class="vip-info">
          <div class="vip-info__name text-overflow">{{item.name}}</div>
          <div class="text-10 vip-rights-num">{{item.freeCourseNum}}门课程 {{item.freeClassroomNum}}个班级</div>
          <div class="text-10">
            {{buyType === 'year' ? `${item.yearPrice}元 / 年` : `${item.monthPrice}元 / 月`}}
          </div>
        </div>
      </swiper-slide>
    </swiper>
    <div class="vip-introduce__text" v-if="levels && levels[activeIndex]" v-show="levels[activeIndex].description">
      <header class="title-18 text-center ph20">{{levels[activeIndex].name}}介绍</header>
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
      user: {
        type: Object,
        default: () => {
          return {};
        }
      },
      activeIndex: '',
    },
    data() {
      const that = this;
      return {
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
              that.$emit('update:activeIndex', this.activeIndex);
            }
          }
        }
      }
    },
    computed: {
      swiper() {
        return this.$refs.mySwiper.swiper
      }
    },
    created() {
      const query = Object.keys(this.$route.query);
      if (!query.includes('vipSeq')) {
        this.setActiveIndex(0);
      } else {
        this.setActiveIndex(Number(this.$route.query.vipSeq));
      }
    },
    updated() {
      this.swiper.slideTo(this.activeIndex, 1000, false)
    },
    methods: {
      vipPopShow() {
        this.$emit('vipOpen', true);
      },
      setActiveIndex(index) {
        this.$emit('update:activeIndex', index);
      }
    }
  }

</script>
