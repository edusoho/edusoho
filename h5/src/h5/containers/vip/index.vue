<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading" />

    <div class="vip-swiper">
      <swiper class="swiper" :options="swiperOption">
        <swiper-slide v-for="(item, index) in levels" :key="index">
          <img class="vip-swiper__img" :src="item.background" />
          <div class="vip-user" v-if="user">
            <div class="vip-user__img">
              <img :src="user.avatar.large" />
            </div>
            <span class="vip-user__name">{{ user.nickname }}</span>
          </div>
        </swiper-slide>
      </swiper>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';

import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
import 'swiper/css/swiper.css';

export default {
  components: {
    Swiper,
    SwiperSlide,
  },
  data() {
    return {
      swiperOption: {
        loop: false,
        centeredSlides: true,
        slidesPerView: 1.28,
        observer: true,
        observeParents: true,
      },
      user: {
        avatar: {},
      },
      levels: [
        {
          courses: {
            data: [],
          },
          classrooms: {
            data: [],
          },
        },
      ],
    };
  },
  computed: {
    ...mapState(['isLoading', 'vipSwitch']),
  },
  created() {
    this.getVipDetail();
  },
  methods: {
    getVipDetail() {
      // const queryId = this.$route.query.id;
      Api.getVipDetail().then(res => {
        this.user = res.vipUser.user;
        this.levels = res.levels;
      });
    },
  },
};
</script>
