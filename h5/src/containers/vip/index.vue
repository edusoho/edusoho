<template>
  <div class="vip-detail">
    <div class="user-section gray-border-bottom clearfix">
      <div v-if="user">
        <img class='user-img' :src="user.avatar.large" />
        <div class="user-middle">
          <div class='user-name'>{{ user.nickname }}</div>
          <span class='user-vip' v-if="vipInfo">
            <img :class="['vip-img', vipDated ? 'vip-expired' : '']" :src="vipInfo.icon">
            <span v-if="!vipDated">{{ vipInfo.vipName }}</span>
            <span class="grey" v-else>会员已过期</span>
          </span>
          <router-link to="/vip" class='user-vip' v-else>
            您还不是会员
          </router-link>
        </div>
        <div class="vip-status" v-if="vipInfo">
          <div class="vip-status__btn" @click="vipPopShow = true" v-if="btnStatus">{{ vipDated ? '重新开通' : btnStatus }}</div>
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
      ref="joinBtn"
      :levels="levels"
      :user="user"
      :buyType="buyType"
      :isVip="vipData.vipUser.vip"
      @activeIndex="activeIndex"
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

    <div v-if="bottomBtnShow" class="btn-join-bottom" @click="vipPopShow = true">立即{{ btnStatus }}</div>
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
    ...mapState(['vipSettings']),
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
    Api.getVipLevels().then((res) => {
      let levelId = Number(res[0].id);
      const routeQuery = Object.keys(this.$route.query);

      if (routeQuery.includes('vipLevelId')) {
        levelId = this.$route.query.vipLevelId
      }

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
        for (var i = 0; i < this.levels.length; i++) {
          const item = res.levels[i];
          this.priceItems = [
            ...this.priceItems,
            getPriceItems(this.vipSettings.buyType, item.monthPrice, item.yearPrice)
          ];
        }
        // currentLevelIndex要放在levels数据之后
        if (!routeQuery.includes('vipSeq')) {
          this.currentLevelIndex = 0
        } else {
          this.currentLevelIndex = Number(this.$route.query.vipSeq)
        }
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
    activeIndex(index) {
      this.currentLevelIndex = index;
    },
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
      this.vipPopShow = true;
    },
    handleScroll () { // 执行函数
      let topSize = this.$refs.joinBtn.$el.getBoundingClientRect().bottom;
      if (topSize < 45) {
        this.bottomBtnShow = true;
      } else {
        this.bottomBtnShow = false;
      }
    }
  }
}
</script>
