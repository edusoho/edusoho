<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading" />
    <div v-if="loaded" class="user-section gray-border-bottom clearfix">
      <div v-if="user">
        <img v-if="user.avatar" :src="user.avatar.large" class="user-img" />
        <div class="user-middle">
          <div class="user-name">{{ user.nickname }}</div>
          <span v-if="vipInfo" class="user-vip vip-level text-overflow ">
            <img
              v-if="vipInfo.icon"
              :class="['vip-img', vipDated ? 'vip-expired' : '']"
              :src="vipInfo.icon"
            />
            <span v-if="!vipDated">{{ vipInfo.vipName }}</span>
            <span v-else class="grey vip-name vip-name-short text-overflow">{{ vipInfo.vipName }}已过期</span>
          </span>
          <span v-else class="user-vip">您还不是会员</span>
        </div>
        <div v-if="vipInfo" class="vip-status">
          <div
            v-if="btnStatus"
            ref="joinBtnTop"
            class="vip-status__btn"
            @click="popShow"
          >{{ vipDated ? '重新开通' : btnStatus }}</div>
          <div
            v-if="vipDeadline"
            :class="['vip-status__deadline', btnStatus ? '' : 'deadline-middle']"
          >{{ vipDeadline }} 到期</div>
        </div>
      </div>
      <router-link v-else :to="{path: '/login', query: { redirect : '/vip'}}">
        <img class="user-img" src="static/images/avatar.png" />
        <div class="user-middle single-middle">
          <div :class="['user-vip', !user ? 'text-middle' : '']">立即登录，查看会员权益</div>
        </div>
      </router-link>
    </div>

    <!-- 会员轮播 -->
    <vip-introduce
      ref="joinBtnBottom"
      :levels="levels"
      :user="user"
      :buy-type="buyType"
      :is-vip="vipUser.vip"
      :active-index.sync="currentLevelIndex"
      @vipOpen="vipOpen"
    />

    <a v-if="isShowInviteUrl" :href="inviteUrl">
      <div class="coupon-code-entrance">
        邀请好友购买
        <i class="van-icon van-icon-arrow pull-right" />
        <i class="pull-right">赚 {{ drpSetting.minDirectRewardRatio }}%</i>
      </div>
    </a>

    <!-- 会员免费课程 -->
    <e-course-list
      v-if="courseData"
      :course-list="courseData"
      :vip-name="levels[currentLevelIndex].name"
      :more-type="'vip'"
      :level-id="Number(levels[currentLevelIndex].id)"
      :type-list="'course_list'"
      class="gray-border-bottom"
    />

    <!-- 会员免费班级 -->
    <e-course-list
      v-if="classroomData"
      :more-type="'vip'"
      :level-id="Number(levels[currentLevelIndex].id)"
      :course-list="classroomData"
      :vip-name="levels[currentLevelIndex].name"
      :type-list="'classroom_list'"
      class="gray-border-bottom"
    />

    <!-- 加入会员 -->
    <e-popup
      v-if="priceItems[currentLevelIndex]"
      :show.sync="vipPopShow"
      :title="btnStatus + levels[currentLevelIndex].name"
      class="vip-popup"
      content-class="vip-popup__content"
    >
      <div class="vip-popup__header text-14">选择{{ btnStatus }}时长</div>
      <div class="vip-popup__body">
        <van-row gutter="20">
          <van-col v-for="(item, index) in priceItems[currentLevelIndex]" :key="index" span="8">
            <price-item
              :item="item"
              :index="index"
              :class="{ active: index === activePriceIndex }"
              :vip-buy-type="vipSettings.buyType"
              @selectItem="selectPriceItem($event, index)"
            />
          </van-col>
        </van-row>
      </div>
      <div
        :class="{ disabled: activePriceIndex < 0 }"
        class="btn-join-bottom"
        @click="joinVip"
      >确认{{ btnStatus }}</div>
    </e-popup>

    <div
      v-if="bottomBtnShow && btnStatus && !vipDated"
      class="btn-join-bottom"
      @click="popShow"
    >立即{{ btnStatus }}</div>
  </div>
</template>
<script>
import EPopup from "@/components/popup";
import Api from "@/api";
import introduce from "./introduce";
import priceItem from "./vip-price-item";
import courseList from "&/components/e-course-list/e-course-list";
import getPriceItems from "../../config/vip-price-config";
import { formatFullTime, getOffsetDays } from "@/utils/date-toolkit.js";
import { mapState } from "vuex";
import { Toast } from "vant";
import * as types from "@/store/mutation-types";
import qs from "qs";

export default {
  components: {
    EPopup,
    priceItem,
    "vip-introduce": introduce,
    "e-course-list": courseList
  },
  data() {
    return {
      loaded: false,
      user: {},
      vipInfo: null,
      vipUser: {},
      levels: [
        {
          courses: {
            data: []
          },
          classrooms: {
            data: []
          }
        }
      ],
      currentLevelIndex: 0,
      activePriceIndex: -1,
      vipPopShow: false,
      priceItems: [],
      buyType: "month",
      bottomBtnShow: false,
      orderParams: {
        unit: "month",
        num: 0
      },
      isShowInviteUrl: false, // 是否显示邀请链接
      drpSetting: {}, // Drp设置信息
      bindAgencyRelation: {} // 分销代理商绑定信息
    };
  },
  computed: {
    ...mapState(["vipSettings", "isLoading", "vipSwitch"]),
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
      const dataFormat = {
        items: [],
        title: "会员课程",
        source: {},
        limit: 4
      };
      dataFormat.items = data;
      return dataFormat;
    },
    classroomData() {
      const data = this.levels[this.currentLevelIndex].classrooms.data;
      if (data.length == 0) return false;
      const dataFormat = {
        items: [],
        title: "会员班级",
        source: {},
        limit: 4
      };
      dataFormat.items = data;
      return dataFormat;
    },
    vipDeadline() {
      if (!Object.values(this.vipInfo).length) return "";
      const time = new Date(this.vipInfo.deadline);
      return formatFullTime(time);
    },
    btnStatus() {
      if (!this.vipInfo) return "开通";
      const currentSeq = Number(this.levels[this.currentLevelIndex].seq);
      const userSeq = this.vipInfo.seq;
      if (userSeq > currentSeq) return "";
      if (this.vipDated) return "开通";
      return userSeq < currentSeq ? "升级" : "续费";
    },
    leftDays() {
      if (!Object.values(this.vipInfo).length) return false;
      const todayStamp = new Date().getTime();
      const deadlineStamp = new Date(this.vipInfo.deadline).getTime();
      return getOffsetDays(todayStamp, deadlineStamp) + 1;
    },
    inviteUrl() {
      const params = {
        type: "vip",
        id: this.levels[this.currentLevelIndex].id,
        merchant_id: this.drpSetting.merchantId
      };
      return (
        this.drpSetting.distributor_template_url + "?" + qs.stringify(params)
      );
    }
  },
  created() {
    if (!this.vipSwitch) {
      this.$router.push({ name: "find" });
      return;
    }
    this.getVipLevels();
    this.showInviteUrl();
    this.getDrpSetting();

    setTimeout(() => {
      window.scrollTo(0, 0);
    }, 100);
  },
  mounted() {
    window.addEventListener("scroll", this.handleScroll, true);
  },
  beforeDestroy() {
    window.removeEventListener("scroll", this.handleScroll, true);
  },
  methods: {
    getVipLevels() {
      Api.getVipLevels({ disableLoading: false })
        .then(res => {
          if (!res.length) {
            this.$router.push({ name: "find" });
            return;
          }
          let levelId = res[0].id;
          this.getVipDetail(levelId);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    getVipDetail(levelId) {
      const queryId = this.$route.query.id;
      Api.getVipDetail({
        query: {
          levelId: levelId
        }
      }).then(res => {
        this.vipUser = res.vipUser || {};
        this.levels = res.levels;
        this.vipInfo = this.vipUser.vip;
        this.user = this.vipUser.user;
        this.buyType = this.vipSettings.buyType;
        // 更新用户会员数据
        const userInfo = this.userInfo;
        userInfo.vip = this.vipInfo;
        this.$store.commit(types.USER_INFO, userInfo);
        // 路由传值vipId > 用户当前等级 > 最低会员等级
        levelId = this.vipUser.vip
          ? this.vipUser.vip.levelId
          : res.levels[0].id;
        levelId = isNaN(queryId) ? levelId : queryId;

        this.getPriceItems( res.levels);
        this.getVipIndex(levelId,res.levels);

        this.loaded = true;
      });
    },
    getPriceItems(levels){
      for (var i = 0; i < this.levels.length; i++) {
          const item = levels[i];
          this.priceItems = [
            ...this.priceItems,
            getPriceItems(
              this.vipSettings.buyType,
              item.monthPrice,
              item.yearPrice
            )
          ];
        }
    },
    getVipIndex(levelId,levels){
       // currentLevelIndex要放在levels数据之后
        let vipIndex = 0;
        const vipLevel =levels.find((level, index) => {
          if (level.id === levelId) {
            vipIndex = index;
            return level;
          }
        });
        this.currentLevelIndex = vipIndex;
    },
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
        name: "order",
        params: {
          id: this.levels[this.currentLevelIndex].id,
          unit: this.orderParams.unit,
          num: this.orderParams.num,
          type: this.btnStatus
        },
        query: {
          targetType: "vip"
        }
      });
    },
    vipOpen() {
      if (!this.user) {
        this.$router.push({
          path: "/login",
          query: {
            redirect: "/vip"
          }
        });
        return;
      }
      this.vipPopShow = true;
    },
    handleScroll() {
      // 执行函数
      if (!this.btnStatus) return;
      let topSize = "";
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
          path: "/login",
          query: {
            redirect: "/vip"
          }
        });
        return;
      }

      // 会员升级
      if (this.btnStatus === "升级") {
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
        this.getVipIndex(this.vipInfo.levelId,this.levels)
      }
      this.vipPopShow = true;
    },
    showInviteUrl() {
      Api.hasDrpPluginInstalled().then(res => {
        if (!res.Drp) {
          this.isShowInviteUrl = false;
          return;
        }

        Api.getAgencyBindRelation().then(data => {
          if (!data.agencyId) {
            this.isShowInviteUrl = false;
            return;
          }
          this.bindAgencyRelation = data;
          this.isShowInviteUrl = true;
        });
      });
    },
    getDrpSetting() {
      Api.getDrpSetting().then(data => {
        this.drpSetting = data;
      });
    }
  }
};
</script>
