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
      <div class="vip-open">
        <swiper :options="vipOpenSwiperOption">
          <template v-for="item in currentVipInfo.sellModes">
            <swiper-slide :key="item.id">
              <price-item
                :item="item"
                :activePriceId="activePriceId"
                @click="clickPriceItem(item.id)"
              />
            </swiper-slide>
          </template>
        </swiper>

        <div class="vip-open__buy" @click="clickVipBuy">{{ vipBuyStatu }}</div>
      </div>
    </div>

    <!-- 专属权益 -->
    <div class="vip-sec">
      <div class="vip-sec__title">
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
        <span class="vip-sec__text">专属权益</span>
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
      </div>
      <div class="vip-interest">
        <div
          class="vip-interest__item"
          v-if="currentVipInfo.courses.data.length"
        >
          <div class="vip-interest__item__img">
            <img src="static/images/vip/vip_course.png" />
          </div>
          <div class="vip-interest__item__title">会员课程</div>
          <div class="vip-interest__item__total">
            {{ currentVipInfo.courses.paging.total }}
            <span class="company">个</span>
          </div>
        </div>
        <div
          class="vip-interest__item"
          v-if="currentVipInfo.classrooms.data.length"
        >
          <div class="vip-interest__item__img">
            <img src="static/images/vip/vip_classroom.png" />
          </div>
          <div class="vip-interest__item__title">会员班级</div>
          <div class="vip-interest__item__total">
            {{ currentVipInfo.classrooms.paging.total }}
            <span class="company">个</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 专属介绍 -->
    <div class="vip-sec">
      <div class="vip-sec__title">
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
        <span class="vip-sec__text">专属介绍</span>
        <div class="vip-sec__style">
          <span class="style style--first"></span>
          <span class="style style--second"></span>
          <span class="style style--third"></span>
        </div>
      </div>
      <div class="vip-introduce"></div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types';

import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
import 'swiper/css/swiper.css';

import PriceItem from './price-item';

export default {
  components: {
    Swiper,
    SwiperSlide,
    PriceItem,
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
            this.getActivePriceId();
          },
        },
      },
      vipOpenSwiperOption: {
        slidesPerView: 3.1,
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
      activePriceId: 0,
    };
  },
  computed: {
    ...mapState(['isLoading', 'vipSwitch']),
    ...mapState({
      userInfo: state => state.user,
    }),

    currentVipInfo() {
      return this.levels[this.activeIndex];
    },

    vipBuyStatu() {
      return '续费12个月会员特权';
    },
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
      this.getActivePriceId();
    },

    // 首次进入，切换到对应会员
    initSwiperActiveIndex() {
      this.$nextTick(() => {
        this.$refs.mySwiper.$swiper.slideTo(this.activeIndex, 1000);
      });
    },

    // 开通时长默认选中第一个
    getActivePriceId() {
      const { sellModes } = this.levels[this.activeIndex];
      this.activePriceId = sellModes.length > 0 ? sellModes[0].id : 0;
    },

    clickPriceItem(value) {
      this.activePriceId = value;
    },

    // 购买 vip
    clickVipBuy() {
      console.log('购买 vip');
    },
  },
};
</script>
