<template>
  <div class="vip-detail">
    <e-loading v-if="isLoading"></e-loading>
    <div class="user-section gray-border-bottom clearfix">
      <div v-if="user">
        <img class='user-img' :src="user.avatar.large" />
        <div class="user-middle">
          <div class='user-name'>{{ user.nickname }}</div>
          <span class='user-vip' v-if="vipInfo">
            <img :class="['vip-img', vipDated ? 'vip-expired' : '']" :src="vipInfo.icon">
            <span v-if="!vipDated">{{ vipInfo.vipName }}</span>
            <span class="grey" v-else>{{ vipInfo.vipName }}已过期</span>
          </span>
          <router-link to="/vip" class='user-vip' v-else>
            您还不是会员
          </router-link>
        </div>
        <div class="vip-status" v-if="vipInfo">
          <div class="vip-status__btn" ref="joinBtnTop" @click="popShow" v-if="btnStatus">{{ vipDated ? '重新开通' : btnStatus }}</div>
          <div :class="['vip-status__deadline', btnStatus ? '' : 'deadline-middle']">{{ vipDeadline }} 到期</div>
        </div>
      </div>
      <router-link :to="{path: '/login', query: { redirect : '/vip'}}" v-else>
        <img class='user-img' src="static/images/avatar.png" />
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
      :isVip="vipData.vipUser.vip"
      :activeIndex.sync="currentLevelIndex"
      @vipOpen="vipOpen">
    </vip-introduce>

    <!-- 会员免费课程 -->
    <e-course-list
      v-if="courseData"
      class="gray-border-bottom"
      :courseList="courseData"
      :vipName="levels[this.currentLevelIndex].name"
      :moreType="'vip'"
      :typeList="'course_list'"/>

    <!-- 会员免费班级 -->
    <e-course-list
      v-if="classroomData"
      class="gray-border-bottom"
      :moreType="'vip'"
      :courseList="classroomData"
      :vipName="levels[this.currentLevelIndex].name"
      :typeList="'classroom_list'"/>

    <!-- 加入会员 -->
    <e-popup class="vip-popup" :show.sync="vipPopShow" :title="'开通' + levels[this.currentLevelIndex].name" contentClass="vip-popup__content">
      <div class="vip-popup__header text-14">选择开通时长</div>
      <div class="vip-popup__body">
        <van-row gutter="20">
          <van-col span="8" v-for="(item, index) in priceItems[currentLevelIndex]" :key="index">
            <price-item :item="item" :class="{ active: index === activePriceIndex }"
              @selectItem="selectPriceItem($event, index)" />
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
import { formatFullTime } from '@/utils/date-toolkit.js';
import { mapState } from 'vuex';
import { Toast } from 'vant';

export default {
  components: {
    EPopup,
    priceItem,
    'vip-introduce': introduce,
    'e-course-list': courseList
  },
  data() {
    return {
      user: {
        avatar: {}
      },
      vipInfo: {},
      vipData: {
        vipUser: {}
      },
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
        unit: '',
        num: 0,
      }
    }
  },
  computed: {
    ...mapState(['vipSettings', 'isLoading']),
    vipDated() {
      if (!this.vipInfo) return false;
      const deadLineStamp = new Date(this.vipInfo.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadLineStamp ? true : false;
    },
    courseData() {
      const data = this.levels[this.currentLevelIndex].courses.data;
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
      const time = new Date(this.vipInfo.deadline);
      return formatFullTime(time);
    },
    btnStatus() {
      if (!this.vipInfo) return '开通';
      const currentSeq = Number(this.levels[this.currentLevelIndex].seq);
      const userSeq = this.vipInfo.seq;
      if (userSeq > currentSeq) return false;
      if (this.vipDated) return '开通';
      return userSeq < currentSeq ? '升级' : '续费';
    }
  },
  created() {
    if (!this.vipSettings.enabled) {
      this.$router.push({name: 'find'});
      return;
    }
    Api.getVipLevels().then((res) => {
      const queryId = this.$route.query.id;

      let levelId = res[0].id

      Api.getVipDetail({
        query: {
          levelId: levelId
        }
      }).then((res) => {
        this.vipData = res;
        this.levels = res.levels;
        this.user = res.vipUser.user;
        this.vipInfo = res.vipUser.vip;
        this.buyType = this.vipSettings.buyType;
        // 路由传值vipId > 用户当前等级 > 最低会员等级
        levelId = res.vipUser.vip ? res.vipUser.vip.levelId : res.levels[0].id;
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
      }).catch(err => {
        Toast.fail(err.message)
      })
    })
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
      if (this.activePriceIndex < 0) {
        return;
      }
      // this.vipPopShow = false;
      this.$router.push({
        name: 'order',
        params: {
          id: this.levels[this.currentLevelIndex].id,
          unit: this.orderParams.unit,
          num: this.orderParams.num
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
      if (!this.user || !this.vipData.vipUser.vip) {
        topSize = this.$refs.joinBtnBottom.$el.getBoundingClientRect().bottom;
        num = 45;
      } else {
        topSize = this.$refs.joinBtnTop.getBoundingClientRect().bottom;
      }
      if (topSize < num) {
        this.bottomBtnShow = true;
      } else {
        this.bottomBtnShow = false;
      }
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
    }
  }
}
</script>
