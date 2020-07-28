<template>
  <div class="vip-introduce gray-border-bottom">
    <swiper ref="mySwiper" :options="swiperOption">
      <swiper-slide v-for="(item, index) in levels" :key="index">
        <img :src="item.background" class="card-bg-img" />
        <div class="vip-info">
          <div class="vip-info__name text-overflow">{{ item.name }}</div>
          <div class="text-10 vip-rights-num">
            {{ item.freeCourseNum }}门课程 {{ item.freeClassroomNum }}个班级
          </div>
          <div class="text-10">
            {{
              buyType === 'year'
                ? `${item.yearPrice}元 / 年`
                : `${item.monthPrice}元 / 月`
            }}
          </div>
        </div>
      </swiper-slide>
    </swiper>
    <div
      v-if="levels && levels[activeIndex]"
      v-show="levels[activeIndex].description"
      class="vip-introduce__text"
    >
      <header class="title-18 text-center ph20">
        {{ levels[activeIndex].name }}介绍
      </header>
      <div class="text-content mt20">
        {{ levels[activeIndex].description || '暂无介绍' }}
      </div>
    </div>
    <div v-if="!isVip || !user" class="text-center">
      <div class="btn-join-vip" @click="vipPopShow">开通会员</div>
    </div>
  </div>
</template>

<script>
import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
// import 'swiper/dist/css/swiper.css'
import 'swiper/css/swiper.css';

export default {
  components: {
    Swiper,
    SwiperSlide,
  },
  props: {
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
      default: 'month',
    },
    user: {
      type: Object,
      default: () => {
        return {};
      },
    },
    activeIndex: {
      type: Number,
      default: 0,
    },
  },
  data() {
    const that = this;
    return {
      swiperOption: {
        notNextTick: true,
        loop: false,
        centeredSlides: true,
        spaceBetween: 38,
        slidesPerView: 1.5,
        observer: true,
        observeParents: true,
        on: {
          slideChangeTransitionStart: function() {
            that.$emit('update:activeIndex', this.activeIndex);
          },
        },
      },
    };
  },
  computed: {
    swiper() {
      return this.$refs.mySwiper.$swiper;
    },
  },
  watch: {
    activeIndex(index) {
      this.swiper.slideTo(index, 1000, false);
    },
  },
  methods: {
    vipPopShow() {
      this.$emit('vipOpen', true);
    },
    setActiveIndex(index) {
      this.$emit('update:activeIndex', index);
    },
  },
};
</script>
