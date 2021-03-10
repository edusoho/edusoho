<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading" />

    <!-- 轮播图 -->
    <div class="vip-swiper">
      <swiper class="swiper" ref="mySwiper" :options="swiperOption">
        <swiper-slide v-for="(item, index) in levels" :key="index">
          <img class="vip-swiper__img" :src="item.background" />
          <div class="vip-user" v-if="user">
            <div class="vip-user__img">
              <img :src="user.avatar.large" />
            </div>
            <span class="vip-user__name">{{ user.nickname }}</span>
          </div>
          <div class="vip-info">
            <div class="vip-info__detail">
              <img class="vip-info__icon" :src="item.icon" />
              <span class="vip-info__name">{{ item.name }}</span>
            </div>
            <div class="vip-info__status">{{ item | getVipStatus }}</div>
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <!-- 开通会员 -->
    <div class="vip-sec">
      <div class="vip-sec__title">
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
        <span class="vip-sec__text">选择开通时长</span>
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types';

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
        on: {
          slideChange: () => {
            this.activeIndex = this.$refs.mySwiper.$swiper.activeIndex;
          },
        },
      },
      user: {
        avatar: {},
      },
      vipInfo: null,
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
      activeIndex: 0,
    };
  },
  computed: {
    ...mapState(['isLoading', 'vipSwitch']),
    ...mapState({
      userInfo: state => state.user,
    }),
  },
  created() {
    this.getVipDetail();
  },
  filters: {
    getVipStatus(value) {
      return '您还不是会员，开通享特权';
    },
  },
  methods: {
    getVipDetail() {
      const queryId = this.$route.query.id;
      Api.getVipDetail().then(res => {
        const { levels, vipUser } = res;

        this.levels = levels;
        this.user = vipUser.user;
        this.vipInfo = vipUser.vip;

        const { vip } = vipUser;
        // 更新用户会员数据
        const userInfo = this.userInfo;
        userInfo.vip = vip;
        this.$store.commit(types.USER_INFO, userInfo);

        // 路由传值vipId > 用户当前等级 > 最低会员等级
        let levelId = vip ? vip.levelId : levels[0].id;
        levelId = isNaN(queryId) ? levelId : queryId;

        this.getVipIndex(levelId, levels);
      });
    },

    getVipIndex(levelId, levels) {
      let vipIndex = 0;
      levels.find((level, index) => {
        if (level.id === levelId) {
          vipIndex = index;
          return level;
        }
      });
      this.activeIndex = vipIndex || 0;
      this.initSwiperActiveIndex();
    },

    // 首次进入，切换到对应会员
    initSwiperActiveIndex() {
      this.$nextTick(() => {
        this.$refs.mySwiper.$swiper.slideTo(this.activeIndex, 1000);
      });
    },
  },
};
</script>
