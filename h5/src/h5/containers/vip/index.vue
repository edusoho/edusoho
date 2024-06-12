<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading" />

    <div class="flex px-16 pt-12" v-if="user">
      <img v-if="user.avatar" :src="user.avatar.large" style="width: 40px;height: 40px;border-radius: 50%;" />
      <div class="ml-8 text-text-1" style="color: #464244;">
        <div class="font-bold text-14">{{ user.nickname }}</div>
        <div class="font-normal text-12 opacity-80">{{ vipStatus(currentLevel) }}</div>
      </div>
    </div>

    <!-- 轮播图 -->
    <div class="mt-20 vip-swiper">
      <swiper class="swiper" ref="mySwiper" :options="swiperOption">
        <swiper-slide v-for="(item, index) in levels" :key="index" style="border-radius: 8px;overflow-y: hidden;">
          <div v-if="vipInfo && vipInfo.levelId === item.id" class="current-level-tag">当前等级</div>
          <img :src="item.background || 'static/images/vip_bg.png'" style="width: 100%;border-radius: 8px;" />
          <div class="absolute font-bold text-center text-14" style="top: 36px;left: 20px;">
            <div class="text-text-1">{{ item.name }}</div>
            <img class="inline-block mt-16" style="width: 50px;" :src="item.icon" />
          </div>
          <div class="absolute flex items-center justify-center font-normal text-12"
            @click="$router.push(`/vip/${item.id}/desc`)"
            style="width: 74px;height: 24px;mix-blend-mode: screen;border: 1px solid #fff;border-radius: 16px;right: 16px;bottom: 30px;background-color: white;font-weight: 600;">
            {{ $t('vip.exclusiveIntroduction') }} >
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <!-- 开通会员 -->
    <swiper style="padding: 20px 0 20px 16px;" v-if="!vipUpgradeMode" :options="vipOpenSwiperOption">
      <swiper-slide v-for="item in currentLevel.sellModes" :key="item.id">
        <div class="flex">
          <price-item :item="item" :activePriceId="activePrice.id" @clickPriceItem="clickPriceItem" />
          <div style="width: 16px; height: 80px;background-color: transparent;"></div>
        </div>
      </swiper-slide>
    </swiper>

    <div class="mx-16 my-20 vip-upgrade" v-else>
      <span class="vip-upgrade__deadline">
        {{ $t('vip.memberUpgradePeriodTo') }}：{{ $moment(vipInfo.deadline).format('YYYY/MM/DD') }}
      </span>
    </div>

    <!-- <div class="vip-sec">
      <div
        class="vip-introduce"
        v-html="currentLevel.description || $t('vip.noIntroduction')"
      />
    </div> -->
    <div class="flex px-16 mb-16 text-14" style="color: #4E5969;">
      <div class="mr-24 nav-item" :class="{ 'active': typeList === 'course_list' }" @click="typeList = 'course_list'">
        <div class="relative" style="z-index: 2;">{{ $t('vip.membersCourse') }}</div>
      </div>
      <div class="nav-item" :class="{ 'active': typeList === 'classroom_list' }" @click="typeList = 'classroom_list'">
        <div class="relative" style="z-index: 2;">{{ $t('vip.membersClass') }}</div>
      </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 z-20 px-16 py-8 bg-text-1">
      <div class="flex items-center justify-center w-full font-bold text-text-1"
        style="height: 40px; border-radius: 20px; background-color: #E7B15C;" :class="{ disabled: !vipBuyStatus.status }"
        @click="clickVipBuy">
        {{ vipBuyStatus.text }}
      </div>
    </div>

    <van-list
      v-if="typeList === 'course_list' && currentLevel.id"
      v-model="ajaxLoading"
      :finished="currentCourseList.getDataFinished"
      style="padding-bottom: 40px;"
      @load="getVipCourseData">
      <e-row-class v-for="item in currentCourseList.data" :key="item.id"
                   :course="item | courseListData({ ...config, typeList: 'course_list' }, 'new')"
                   :discountType="item.courseSet.discountType" :discount="item.courseSet.discount" :course-type="item.courseSet.type"
                   type-list="course_list" type="price" :showNumberData="showNumberData" />
    </van-list>

    <van-list
      v-if="typeList === 'classroom_list' && currentLevel.id"
      v-model="ajaxLoading"
      :finished="currentClassroomList.getDataFinished"
      style="padding-bottom: 40px;"
      @load="getVipClassroomData">
      <e-row-class v-for="item in currentClassroomList.data" :key="item.id"
        :course="item | courseListData({ ...config, typeList: 'classroom_list' }, 'new')" type-list="classroom_list"
        type="price" :showNumberData="showNumberData" />
    </van-list>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import * as types from '@/store/mutation-types';
import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
import 'swiper/css/swiper.css';
import PriceItem from './price-item';
import courseListData from '@/utils/filter-course.js';
import eRowClass from '&/components/e-row-class/e-row-class';

export default {
  components: {
    Swiper,
    SwiperSlide,
    PriceItem,
    eRowClass,
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
          slideChange: (val) => {
            this.activeIndex = this.swiper.activeIndex;
            this.getActivePrice();
          },
        },
      },
      vipOpenSwiperOption: {
        slidesPerView: 2.4,
      },
      user: {},
      vipInfo: {},
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
      typeList: 'course_list',
      ajaxLoading: false,
      getDataFinished: false,
      vipCourseData: {},
      vipClassroomData: {}
    };
  },
  filters: {
    courseListData,
  },
  computed: {
    ...mapState(['vipSwitch', 'courseSettings', 'classroomSettings']),
    ...mapState({
      userInfo: state => state.user,
      vipOpenStatus: state => state.vip.vipOpenStatus,
      upgradeMode: state => state.vip.upgradeMode,
      showNumberData: state => state.goodsSettings.show_number_data
    }),

    currentCourseList() {
      if (!this.currentLevel) return {}

      if (!this.vipCourseData[this.currentLevel.id]) return {}

      return this.vipCourseData[this.currentLevel.id]
    },

    currentClassroomList() {
      if (!this.currentLevel) return {}

      if (!this.vipClassroomData[this.currentLevel.id]) return {}

      return this.vipClassroomData[this.currentLevel.id]
    },

    config() {
      return {
        type: 'price',
        showStudent: this.courseSettings
          ? Number(this.courseSettings.show_student_num_enabled)
          : true,
        classRoomShowStudent: this.classroomSettings
          ? this.classroomSettings.show_student_num_enabled
          : true,
      };
    },

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
      return this.levels[this.activeIndex] || {};
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

    vipBuyStatus() {
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

    getVipCourseData() {
      if (!this.currentLevel) return

      const courseList = this.vipCourseData[this.currentLevel.id] || { offset: 0, data: [] }

      if (courseList.getDataFinished) return

      const params = { levelId: this.currentLevel.id, offset: courseList.offset }

      Api.getVipCourses({ params }).then(({ data, paging }) => {
        courseList.data = [...courseList.data, ...data]
        courseList.offset = courseList.data.length
        courseList.paging = paging

        if (courseList.data.length == paging.total) {
          courseList.getDataFinished = true
        }

        this.$set(this.vipCourseData, this.currentLevel.id, courseList)
      }).finally(() => {
        this.ajaxLoading = false
      })
    },

    getVipClassroomData() {
      if (!this.currentLevel) return

      const classroomList = this.vipClassroomData[this.currentLevel.id] || { offset: 0, data: [] }

      if (classroomList.getDataFinished) return

      const params = { levelId: this.currentLevel.id, offset: classroomList.offset }

      Api.getVipClasses({ params }).then(({ data, paging }) => {
        classroomList.data = [...classroomList.data, ...data]
        classroomList.offset = classroomList.data.length
        classroomList.paging = paging

        if (classroomList.data.length == paging.total) {
          classroomList.getDataFinished = true
        }

        this.$set(this.vipClassroomData, this.currentLevel.id, classroomList)
      }).finally(() => {
        this.ajaxLoading = false
      })
    },

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

      if (!this.vipBuyStatus.status) return;

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
          type: this.vipBuyStatus.type,
        },
        query: {
          targetType: 'vip',
        },
      });
    },
  }
};
</script>

<style lang="scss" scoped>
.nav-item {
  position: relative;

  &.active {
    font-weight: bold;
    color: #1D2129;

    &:after {
      content: '';
      position: absolute;
      z-index: 1;
      width: 36px;
      height: 12px;
      left: -2px;
      bottom: -1px;

      background: linear-gradient(90deg, #F3CA98 2.04%, rgba(242, 202, 151, 0) 100%);
      border-radius: 21px;
    }
  }
}

.current-level-tag {
  position: absolute;
  top: 0;
  left: 0;
  width: 56px;
  height: 18px;
  color: #494444;
  font-size: 12px;
  font-weight: 500;
  line-height: 18px;
  text-align: center;
  background: linear-gradient(98.69deg, #EAB86A 16.63%, rgba(234, 184, 106, 0.63) 109.86%);
  border-radius: 6px 0px;
}
</style>
