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
      currentLevelIndex: 0,
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
      // currentLevelIndex 要放在 levels 数据之后
      const vipIndex = levels.find((level, index) => {
        if (level.id === levelId) {
          return index;
        }
      });
      this.currentLevelIndex = vipIndex;
    },
  },
};
</script>
