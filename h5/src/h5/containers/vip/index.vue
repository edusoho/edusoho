<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading" />

    <!-- 轮播图 -->
    <div class="vip-swiper">
      <swiper class="swiper" ref="mySwiper" :options="swiperOption">
        <swiper-slide v-for="(item, index) in levels" :key="index">
          <img class="vip-swiper__img" :src="item.background" />
          <div class="vip-user" v-if="user">
            <div class="vip-user__img" v-if="user.avatar">
              <img :src="user.avatar.large" />
            </div>
            <span class="vip-user__name">{{ user.nickname }}</span>
          </div>
          <div class="vip-info">
            <div class="vip-info__detail">
              <img class="vip-info__icon" :src="item.icon" />
              <span class="vip-info__name">{{ item.name }}</span>
            </div>
            <div class="vip-info__status">{{ vipStatus(item) }}</div>
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <!-- 开通会员 -->
    <div class="vip-sec">
      <module-title
        :title="userLevelStatus == 'upgrade' ? $t('vip.memberUpgrade') : $t('vip.chooseTheActivationTime')"
      />
      <div class="vip-open">
        <swiper v-if="!vipUpgradeMode" :options="vipOpenSwiperOption">
          <template v-for="item in currentLevel.sellModes">
            <swiper-slide :key="item.id">
              <price-item
                :item="item"
                :activePriceId="activePrice.id"
                @clickPriceItem="clickPriceItem"
              />
            </swiper-slide>
          </template>
        </swiper>

        <div class="vip-upgrade" v-else>
          <span class="vip-upgrade__deadline">
            {{ $t('vip.memberUpgradePeriodTo') }}：{{ $moment(vipInfo.deadline).format('YYYY/MM/DD') }}
          </span>
        </div>

        <div
          class="vip-open__buy"
          :class="{ disabled: !vipBuyStatu.status }"
          @click="clickVipBuy"
        >
          {{ vipBuyStatu.text }}
        </div>
      </div>
    </div>

    <!-- 专属权益 -->
    <div class="vip-sec">
      <module-title :title="$t('vip.exclusiveRights')" />
      <div class="vip-interest">
        <div class="vip-interest__item" v-if="currentLevel.courses.data.length">
          <div class="vip-interest__item__img">
            <img src="static/images/vip/vip_course.png" />
          </div>
          <div class="vip-interest__item__title">{{ $t('vip.membersCourse') }}</div>
          <div class="vip-interest__item__total">
            {{ currentLevel.courses.paging.total }}
            <span class="company">{{ $t('vip.piece') }}</span>
          </div>
        </div>
        <div
          class="vip-interest__item"
          v-if="currentLevel.classrooms.data.length"
        >
          <div class="vip-interest__item__img">
            <img src="static/images/vip/vip_classroom.png" />
          </div>
          <div class="vip-interest__item__title">{{ $t('vip.membersClass') }}</div>
          <div class="vip-interest__item__total">
            {{ currentLevel.classrooms.paging.total }}
            <span class="company">{{ $t('vip.piece') }}</span>
          </div>
        </div>
        <div
          class="vip-interest__empty"
          v-if="
            !currentLevel.courses.data.length &&
              !currentLevel.classrooms.data.length
          "
        >
          <img src="static/images/vip/empty.png" alt="" />
          <p>{{ $t('vip.noExclusiveRightsAndInterests') }}</p>
        </div>
      </div>
    </div>

    <!-- 专属介绍 -->
    <div class="vip-sec">
      <module-title :title="$t('vip.exclusiveIntroduction')" />
      <div
        class="vip-introduce"
        v-html="currentLevel.description || $t('vip.noIntroduction')"
      />
    </div>

    <!-- 专属特权 -->
    <div class="vip-sec">
      <module-title :title="$t('vip.exclusivePrivileges')" />
      <div class="vip-privilege">
        <!-- 会员免费课程 -->
        <e-course-list
          v-if="courseData"
          :course-list="courseData"
          :vip-name="currentLevel.name"
          :more-type="'vip'"
          :level-id="Number(currentLevel.id)"
          :type-list="'course_list'"
          class="vip-course-list"
        />

        <!-- 会员免费班级 -->
        <e-course-list
          v-if="classroomData"
          :more-type="'vip'"
          :level-id="Number(currentLevel.id)"
          :course-list="classroomData"
          :vip-name="currentLevel.name"
          :type-list="'classroom_list'"
          class="vip-course-list"
        />
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import * as types from '@/store/mutation-types';

import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
import 'swiper/css/swiper.css';

import ModuleTitle from './module-title';
import PriceItem from './price-item';
import ECourseList from '&/components/e-course-list/e-course-list';

export default {
  components: {
    Swiper,
    SwiperSlide,
    ModuleTitle,
    PriceItem,
    ECourseList,
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
            this.activeIndex = this.swiper.activeIndex;
            this.getActivePrice();
          },
        },
      },
      vipOpenSwiperOption: {
        slidesPerView: 3.1,
      },
      user: {},
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
      activePrice: null,
      isLoading: false,
    };
  },
  computed: {
    ...mapState(['vipSwitch']),
    ...mapState({
      userInfo: state => state.user,
      vipOpenStatus: state => state.vip.vipOpenStatus,
      upgradeMode: state => state.vip.upgradeMode,
    }),

    vipDated() {
      if (!this.vipInfo) {
        return true;
      }
      const deadLineStamp = new Date(this.vipInfo.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadLineStamp;
    },

    swiper() {
      return this.$refs.mySwiper.$swiper;
    },

    currentLevel() {
      return this.levels[this.activeIndex];
    },

    userLevelStatus() {
      const userSeq = this.vipInfo ? this.vipInfo.seq : 0;
      const { seq } = this.currentLevel;

      if (userSeq === 0 || this.vipDated) {
        return 'opening';
      }
      if (userSeq === seq) {
        return 'renew';
      }
      if (userSeq < seq) {
        return 'upgrade';
      }
      return 'low';
    },

    vipBuyStatu() {
      const title = this.activePrice ? this.activePrice.title : '';
      const actions = {
        opening: {
          text: this.$t('vip.openPrivileges', { title: title }),
          status: true,
          type: this.$t('vip.open')
        },
        renew: {
          text: this.$t('vip.renewPrivileges', { title: title }),
          status: true,
          type: this.$t('vip.renew')
        },
        upgrade: {
          text: this.$t('vip.upgradeToCurrentMemberPrivileges'),
          status: true,
          type: this.$t('vip.upgrade')
        },
        low: {
          text: this.$t('vip.rankLowerThanPurchasedMembers'),
          status: false,
          type: this.$t('vip.lowerThan')
        },
      };

      return actions[this.userLevelStatus];
    },

    courseData() {
      const { data, paging } = this.currentLevel.courses;
      if (data.length == 0) return false;
      const dataFormat = {
        items: [],
        title: this.$t('vip.membersCourseTotal', { total: paging.total }),
        source: {},
        limit: 4,
        vipCenter: true,
      };
      dataFormat.items = data.slice(0, 3);
      return dataFormat;
    },

    classroomData() {
      const { data, paging } = this.currentLevel.classrooms;
      if (data.length == 0) return false;
      const dataFormat = {
        items: [],
        title: this.$t('vip.membersClassTotal', { total: paging.total }),
        source: {},
        limit: 4,
        vipCenter: true,
      };
      dataFormat.items = data.slice(0, 3);
      return dataFormat;
    },

    vipUpgradeMode() {
      return (
        this.userLevelStatus == 'upgrade' && this.upgradeMode == 'remain_period'
      );
    },
  },
  async created() {
    this.isLoading = true;

    // 未登录跳转登录页面
    if (!this.$store.state.token) {
      this.$router.replace({
        name: 'login',
        query: {
          redirect: this.$route.fullPath,
        },
      });
      return;
    }

    if (this.vipOpenStatus === null) {
      await this.getVipOpenStatus();
    }

    if (!this.vipOpenStatus) {
      this.$router.push({
        path: '/',
        query: {
          redirect: this.$route.fullPath,
        },
      });
      return;
    }
    this.getVipDetail();
  },
  methods: {
    ...mapActions('vip', ['getVipOpenStatus']),

    getVipDetail() {
      const queryId = this.$route.query.id;
      Api.getVipDetail().then(res => {
        this.isLoading = false;
        const { levels, vipUser } = res;

        this.levels = levels;
        this.user = vipUser ? vipUser.user : null;
        this.vipInfo = vipUser.vip;

        const vip = vipUser ? vipUser.vip : null;
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
      this.getActivePrice();
    },

    // 轮播图 vip 状态
    vipStatus(data) {
      if (!this.vipInfo) {
        return this.$t('vip.youAreNotAVipYet')
      }
      const seq = Number(this.vipInfo.seq);
      const deadline = this.vipInfo.deadline;
      const currentVipSeq = Number(data.seq);

      if (this.vipDated) {
        return seq === currentVipSeq
          ? this.$t('vip.membershipExpired')
          : this.$t('vip.youAreNotAVipYet');
      }

      if (seq === currentVipSeq) {
        return `${this.$t('vip.membershipUntil')}：${this.$moment(deadline).format('YYYY/MM/DD')}`;
      }
      if (seq > currentVipSeq) {
        return this.$t('vip.rankLowerThanPurchasedMembers');
      }
      return this.$t('vip.notMemberPleaseUpgrade');
    },

    // 首次进入，切换到对应会员
    initSwiperActiveIndex() {
      this.$nextTick(() => {
        this.swiper.slideTo(this.activeIndex, 0);
      });
    },

    // 开通时长默认选中第一个
    getActivePrice() {
      const { sellModes } = this.levels[this.activeIndex];
      this.activePrice = sellModes.length > 0 ? sellModes[0] : null;
    },

    clickPriceItem(value) {
      this.activePrice = value;
    },

    clickVipBuy() {
      if (!this.user) {
        this.$router.push({
          path: '/login',
          query: {
            redirect: '/vip',
          },
        });
        return;
      }

      if (!this.vipBuyStatu.status) return;

      // 没有价格选项，不能创建订单
      if (!this.activePrice) {
        return;
      }

      this.$router.replace({
        name: 'order',
        params: {
          id: this.activePrice.id,
          unit: this.activePrice.specUnit,
          num: this.activePrice.duration,
          type: this.vipBuyStatu.type,
        },
        query: {
          targetType: 'vip',
        },
      });
    },
  },
};
</script>
