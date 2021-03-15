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
      <module-title title="选择开通时长" />
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
      <module-title title="专属权益" />
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
      <module-title title="专属介绍" />
      <div
        class="vip-introduce"
        v-html="currentVipInfo.description || '暂无介绍'"
      />
    </div>

    <!-- 专属特权 -->
    <div class="vip-sec">
      <module-title title="专属特权" />
      <div class="vip-privilege">
        <!-- 会员免费课程 -->
        <e-course-list
          v-if="courseData"
          :course-list="courseData"
          :vip-name="currentVipInfo.name"
          :more-type="'vip'"
          :level-id="Number(currentVipInfo.id)"
          :type-list="'course_list'"
          class="vip-course-list"
        />

        <!-- 会员免费班级 -->
        <e-course-list
          v-if="classroomData"
          :more-type="'vip'"
          :level-id="Number(currentVipInfo.id)"
          :course-list="classroomData"
          :vip-name="currentVipInfo.name"
          :type-list="'classroom_list'"
          class="vip-course-list"
        />
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
            this.getActivePriceId();
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
      activePriceId: 0,
    };
  },
  computed: {
    ...mapState(['isLoading', 'vipSwitch']),
    ...mapState({
      userInfo: state => state.user,
    }),

    swiper() {
      return this.$refs.mySwiper.$swiper;
    },

    currentVipInfo() {
      return this.levels[this.activeIndex];
    },

    vipBuyStatu() {
      const { seq } = this.vipInfo;
      const currentVipSeq = this.currentVipInfo.seq;

      if (seq === currentVipSeq) {
        return {
          text: '续费12个月会员特权',
          status: true,
        };
      }
      if (seq > currentVipSeq) {
        return {
          text: '等级低于已购会员',
          status: false,
        };
      }
      return {
        text: '升级为当前会员特权',
        status: true,
      };
    },

    courseData() {
      const { data, paging } = this.currentVipInfo.courses;
      if (data.length == 0) return false;
      const dataFormat = {
        items: [],
        title: `会员课程(${paging.total})`,
        source: {},
        limit: 4,
      };
      dataFormat.items = data.slice(0, 3);
      return dataFormat;
    },

    classroomData() {
      const { data, paging } = this.currentVipInfo.classrooms;
      if (data.length == 0) return false;
      const dataFormat = {
        items: [],
        title: `会员班级(${paging.total})`,
        source: {},
        limit: 4,
      };
      dataFormat.items = data.slice(0, 3);
      return dataFormat;
    },
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
      this.getActivePriceId();
    },

    // 轮播图 vip 状态
    vipStatus(data) {
      if (!this.vipInfo) {
        return '您还不是会员，开通享特权';
      }
      const { seq, deadline } = this.vipInfo;
      const currentVipSeq = data.seq;

      if (seq === currentVipSeq) {
        return `会员有效期至：${this.$moment(deadline).format('YYYY/MM/DD')}`;
      }
      if (seq > currentVipSeq) {
        return '等级低于已购会员';
      }
      return '您还不是该等级会员请升级';
    },

    // 首次进入，切换到对应会员
    initSwiperActiveIndex() {
      this.$nextTick(() => {
        this.swiper.slideTo(this.activeIndex, 1000);
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

      console.log('fasf');
    },
  },
};
</script>
