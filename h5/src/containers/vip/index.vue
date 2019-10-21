<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading"></e-loading>
    <div class="user-section gray-border-bottom clearfix">
      <div v-if="user">
        <img v-if="user.avatar" class="user-img" :src="user.avatar.large" />
        <div class="user-middle">
          <div class="user-name">{{ user.nickname }}</div>
          <span class="user-vip" v-if="vipInfo">
            <img v-if="vipInfo.icon" :class="['vip-img', vipDated ? 'vip-expired' : '']" :src="vipInfo.icon">
            <span v-if="!vipDated">{{ vipInfo.vipName }}</span>
            <span class="grey vip-name vip-name-short text-overflow" v-else>{{ vipInfo.vipName }}已过期</span>
          </span>
          <span class="user-vip" v-else>
            您还不是会员
          </span>
        </div>
        <div class="vip-status" v-if="vipInfo">
          <div class="vip-status__btn" ref="joinBtnTop" @click="popShow" v-if="btnStatus">{{ vipDated ? '重新开通' : btnStatus }}</div>
          <div v-if="vipDeadline" :class="['vip-status__deadline', btnStatus ? '' : 'deadline-middle']">{{ vipDeadline }} 到期</div>
        </div>
      </div>
      <router-link :to="{path: '/login', query: { redirect : '/vip'}}" v-else>
        <img class="user-img" src="static/images/avatar.png" />
        <div class="user-middle single-middle">
          <div :class="['user-vip', !user ? 'text-middle' : '']">
            立即登录，查看会员权益
          </div>
        </div>
      </router-link>
    </div>

    <!-- 会员轮播 -->
    <vip-introduce
      ref="joinBtnBottom"
      :levels="levels"
      :user="user"
      :buyType="buyType"
      :isVip="vipUser.vip"
      :activeIndex.sync="currentLevelIndex"
      @vipOpen="vipOpen">
    </vip-introduce>

    <a v-if="hasDrp" :href="inviteUrl">
      <div class="coupon-code-entrance">邀请好友购买
        <i class="van-icon van-icon-arrow pull-right"></i><i class="pull-right">赚 {{ bindAgencyRelation.directRewardRatio }}%</i>
      </div>
    </a>

    <!-- 会员免费课程 -->
    <e-course-list
      v-if="courseData"
      class="gray-border-bottom"
      :courseList="courseData"
      :vipName="levels[currentLevelIndex].name"
      :moreType="'vip'"
      :levelId="Number(levels[currentLevelIndex].id)"
      :typeList="'course_list'"/>

    <!-- 会员免费班级 -->
    <e-course-list
      v-if="classroomData"
      class="gray-border-bottom"
      :moreType="'vip'"
      :levelId="Number(levels[currentLevelIndex].id)"
      :courseList="classroomData"
      :vipName="levels[currentLevelIndex].name"
      :typeList="'classroom_list'"/>

    <!-- 加入会员 -->
    <e-popup class="vip-popup" v-if="priceItems[currentLevelIndex]"
     :show.sync="vipPopShow" :title="btnStatus + levels[currentLevelIndex].name" contentClass="vip-popup__content">
      <div class="vip-popup__header text-14">选择{{ btnStatus }}时长</div>
      <div class="vip-popup__body">
        <van-row gutter="20">
          <van-col span="8" v-for="(item, index) in priceItems[currentLevelIndex]" :key="index">
            <price-item :item="item" :index="index" :class="{ active: index === activePriceIndex }"
              @selectItem="selectPriceItem($event, index)" :vipBuyType="vipSettings.buyType"/>
          </van-col>
        </van-row>
      </div>
      <div class="btn-join-bottom" :class="{ disabled: activePriceIndex < 0 }" @click="joinVip">确认{{ btnStatus }}</div>
    </e-popup>

    <div v-if="bottomBtnShow && btnStatus && !vipDated" class="btn-join-bottom" @click="popShow">立即{{ btnStatus }}</div>
  </div>
</template>
<script>
import EPopup from '@/components/popup';
import Api from '@/api';
import introduce from './introduce';
import priceItem from './vip-price-item';
import courseList from '../components/e-course-list/e-course-list';
import getPriceItems from '../../config/vip-price-config';
import { formatFullTime, getOffsetDays } from '@/utils/date-toolkit.js';
import { mapState } from 'vuex';
import { Toast } from 'vant';
import * as types from '@/store/mutation-types';
import qs from 'qs';

export default {
  components: {
    EPopup,
    priceItem,
    'vip-introduce': introduce,
    'e-course-list': courseList
  },
  data() {
    return {
      user: {},
      vipInfo: null,
      vipUser: {},
      levels: [{
        courses: {
          data: []
        },
        classrooms: {
          data: []
        }
      }],
      currentLevelIndex: 0,
      activePriceIndex: -1,
      vipPopShow: false,
      priceItems: [],
      buyType: 'month',
      bottomBtnShow: false,
      orderParams: {
        unit: 'month',
        num: 0,
      },
      isShowInviteUrl: false, // 是否显示邀请链接
      drpSetting: {}, // Drp设置信息
      bindAgencyRelation: {}, // 分销代理商绑定信息
    }
  },
  computed: {
    ...mapState(['vipSettings', 'isLoading', 'vipSwitch']),
    ...mapState({ userInfo: state => state.user }),
    vipDated() {
      if (!this.vipInfo) return false;
      const deadlineStamp = new Date(this.vipInfo.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadlineStamp;
    },
    courseData() {
      const data = this.levels[this.currentLevelIndex].courses.data;
      if (data.length == 0) return false;
      let dataFormat = {
        items: [],
        title: '会员课程',
        source: {},
        limit: 4
      }
      dataFormat.items = data;
      return dataFormat;
    },
    classroomData() {
      const data = this.levels[this.currentLevelIndex].classrooms.data;
      if (data.length == 0) return false;
      let dataFormat = {
        items: [],
        title: '会员班级',
        source: {},
        limit: 4
      }
      dataFormat.items = data;
      return dataFormat;
    },
    vipDeadline() {
      if (!Object.values(this.vipInfo).length) return '';
      const time = new Date(this.vipInfo.deadline);
      return formatFullTime(time);
    },
    btnStatus() {
      if (!this.vipInfo) return '开通';
      const currentSeq = Number(this.levels[this.currentLevelIndex].seq);
      const userSeq = this.vipInfo.seq;
      if (userSeq > currentSeq) return '';
      if (this.vipDated) return '开通';
      return userSeq < currentSeq ? '升级' : '续费';
    },
    leftDays() {
      if (!Object.values(this.vipInfo).length) return false;
      const todayStamp = new Date().getTime();
      const deadlineStamp = new Date(this.vipInfo.deadline).getTime();
      return getOffsetDays(todayStamp, deadlineStamp) + 1;
    },
    inviteUrl() {
      let params = {
        type: 'vip',
        id: this.levels[this.currentLevelIndex].id,
        merchant_id: this.bindAgencyRelation.merchantId,
      };
      return this.drpSetting.distributor_template_url + '?' + qs.stringify(params);
    }
  },
  created() {
    if (!this.vipSwitch) {
      this.$router.push({name: 'find'});
      return;
    }
    Api.getVipLevels({ disableLoading: false }).then((res) => {
      if (!res.length) {
        this.$router.push({name: 'find'});
        return;
      }
      const queryId = this.$route.query.id;

      let levelId = res[0].id

      Api.getVipDetail({
        query: {
          levelId: levelId
        }
      }).then((res) => {
        this.vipUser = res.vipUser || {};
        this.levels = res.levels;
        this.user = this.vipUser.user;
        this.vipInfo = this.vipUser.vip;
        this.buyType = this.vipSettings.buyType;
        // 更新用户会员数据
        const userInfo = this.userInfo;
        userInfo.vip = this.vipInfo;
        this.$store.commit(types.USER_INFO, userInfo);
        // 路由传值vipId > 用户当前等级 > 最低会员等级
        levelId = this.vipUser.vip ? this.vipUser.vip.levelId : res.levels[0].id;
        levelId = isNaN(queryId) ? levelId : queryId;

        for (var i = 0; i < this.levels.length; i++) {
          const item = res.levels[i];
          this.priceItems = [
            ...this.priceItems,
            getPriceItems(this.vipSettings.buyType, item.monthPrice, item.yearPrice)
          ];
        }

        // currentLevelIndex要放在levels数据之后
        let vipIndex = 0;
        const vipLevel = res.levels.find((level, index) => {
          if (level.id === levelId) {
            vipIndex = index
            return level;
          }
        });
        this.currentLevelIndex = vipIndex;
      })
    }).catch(err => {
      Toast.fail(err.message)
    })

    this.isShowInviteUrl = this.showInviteUrl();

    if (this.isShowInviteUrl) {
      this.bindAgencyRelation = this.getAgencyBindRelation();
      this.getDrpSetting = this.getDrpSetting();
    }

    setTimeout(() => {
      window.scrollTo(0,0);
    }, 100)
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll, true);
  },
  beforeDestroy() {
    window.removeEventListener("scroll", this.handleScroll, true);
  },
  methods: {
    selectPriceItem(event, index) {
      this.activePriceIndex = index;
      this.orderParams.unit = event.unit;
      this.orderParams.num = event.num;
    },
    joinVip() {
      // 没有价格选项，不能创建订单
      if (this.activePriceIndex < 0) {
        return;
      }

      this.$router.push({
        name: 'order',
        params: {
          id: this.levels[this.currentLevelIndex].id,
          unit: this.orderParams.unit,
          num: this.orderParams.num,
          type: this.btnStatus
        },
        query: {
          targetType: 'vip',
        }
      });
    },
    vipOpen() {
      if (!this.user) {
        this.$router.push({
          path: '/login',
          query: {
            redirect: '/vip'
          }
        })
        return;
      }
      this.vipPopShow = true;
    },
    handleScroll () { // 执行函数
      if (!this.btnStatus) return;
      let topSize = '';
      let num = 0;
      if (!this.user || !this.vipUser.vip) {
        topSize = this.$refs.joinBtnBottom.$el.getBoundingClientRect().bottom;
        num = 45;
      } else {
        topSize = this.$refs.joinBtnTop.getBoundingClientRect().bottom;
      }
      this.bottomBtnShow = topSize < num;
    },
    popShow() {
      if (!this.user) {
        this.$router.push({
          path: '/login',
          query: {
            redirect: '/vip'
          }
        })
        return;
      }

      // 会员升级
      if (this.btnStatus === '升级') {
        const upgradeMinDay = this.vipSettings.upgradeMinDay;
        if (this.leftDays <= upgradeMinDay) {
          Toast(`会员剩余天数小于${upgradeMinDay}天，请先续费后再升级`);
          return;
        }
        this.activePriceIndex = 0;
        this.joinVip();
        return;
      }

      // 会员续费
      if (this.vipDated && this.vipInfo) {
        let vipIndex = 0;
        const vipLevel = this.levels.find((level, index) => {
          if (level.id === this.vipInfo.levelId) {
            vipIndex = index
            return level;
          }
        });
        this.currentLevelIndex = vipIndex
      }
      this.vipPopShow = true;
    },
    showInviteUrl() {
      Api.hasDrpPluginInstalled().then(res => {
        if (!res.Drp) {
          return false;
        }

        Api.getAgencyBindRelation().then(data => {
          if (JSON.stringify(data) == '{}') {
            return false;
          }
          return true;
        })
      })
    },
    getDrpSetting() {
      Api.getDrpSetting().then(data => {
        return data;
      });
    },
    getAgencyBindRelation() {
      Api.getAgencyBindRelation().then(data => {
        return data;
      })
    }
  }
}
</script>
