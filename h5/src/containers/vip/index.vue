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
        <div class="vip-status">
          <div class="vip-status__btn" @click="vipPopShow = true">{{ vipDated ? '重新开通' : btnStatus }}</div>
          <div class="vip-status__deadline">{{ vipDeadline }} 到期</div>
        </div>
      </div>
      <router-link :to="{path: '/login', query: { redirect : '/vip'}}" v-else>
        <img class='user-img' src="statsc/images/avatar.png" />
        <div class="user-middle single-middle">
          <div class='user-vip'>
            立即登录，查看会员权益
          </div>
        </div>
      </router-link>
    </div>

    <!-- 会员轮播 -->
    <vip-introduce :levels="levels" :isVip="vipData.vipUser.vip" @activeIndex="activeIndex"></vip-introduce>

    <!-- 会员免费课程 -->
    <e-course-list
      class="gray-border-bottom"
      :courseList="courseData"
      :typeList="'course_list'"/>

    <!-- 会员免费班级 -->
    <e-course-list
      class="gray-border-bottom"
      :courseList="classroomData"
      :typeList="'classroom_list'"/>

    <!-- 加入会员 -->
    <e-popup class="vip-popup" :show.sync="vipPopShow" :title="'开通' + levels[this.currentLevelIndex].name" contentClass="vip-popup__content">
      <div class="vip-popup__header text-14">选择开通时长</div>
      <div class="vip-popup__body">
        <van-row gutter="20">
          <van-col span="8" v-for="(item, index) in priceItems[currentLevelIndex]" :key="index">
            <price-item :item="item" :class="{ active: index === activePriceIndex }"
              @click.native="selectPriceItem(index)" />
          </van-col>
        </van-row>
      </div>
      <div class="btn-join-bottom" @click="vipPopShow = false">确认{{ btnStatus }}</div>
    </e-popup>

    <div class="btn-join-bottom" @click="vipPopShow = true">立即{{ btnStatus }}</div>
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

export default {
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
      activePriceIndex: 0,
      vipLevelId: this.$router.query ? this.$router.query.vipLevelId : 1,
      vipPopShow: false,
      priceItems: [],
    }
  },
  components: {
    EPopup,
    priceItem,
    'vip-introduce': introduce,
    'e-course-list': courseList
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
      let data = {
        items: [],
        title: '会员课程',
        source: {},
        limit: 4
      }
      data.items = this.levels[this.currentLevelIndex].courses.data;
      return data;
    },
    classroomData() {
      let data = {
        items: [],
        title: '会员班级',
        source: {},
        limit: 4
      }
      data.items = this.levels[this.currentLevelIndex].classrooms.data;
      return data;
    },
    vipDeadline() {
      const time = new Date(this.vipInfo.deadline);
      return formatFullTime(time);
    },
    btnStatus() {
      const currentSeq = this.levels[this.currentLevelIndex].seq;
      const userSeq = this.vipInfo.seq;
      if (userSeq > currentSeq) return;
      if (this.vipDated) return '开通';
      return userSeq < currentSeq ? '升级' : '续费';
    }
  },
  created() {
    Api.getVipDetail({
      query: {
        levelId: this.vipLevelId
      }
    }).then((res) => {
      this.vipData = res;
      this.levels = res.levels;
      this.user = res.vipUser.user;
      this.vipInfo = res.vipUser.vip;
      for (var i = 0; i < this.levels.length; i++) {
        this.priceItems = [
          ...this.priceItems,
          getPriceItems(this.vipSettings.buyType, res.levels[i].monthPrice, res.levels[i].yearPrice)
        ];
      }
    })
  },
  methods: {
    activeIndex(index) {
      this.currentLevelIndex = index;
    },
    selectPriceItem(index) {
      this.activePriceIndex = index;
    }
  }
}
</script>
