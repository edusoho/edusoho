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
          <div class="vip-status__btn">{{ btnStatus }}</div>
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
    <vip-introduce :levels="levels" :isVip="vipData.vipUser.vip" @activeIndex="activeIndex"></vip-introduce>
    <e-course-list
      class="gray-border-bottom"
      :courseList="courseData"
      :typeList="'course_list'"/>
    <e-course-list
      class="gray-border-bottom"
      :courseList="classroomData"
      :typeList="'classroom_list'"/>
    <div class="btn-join-bottom">立即{{ btnStatus }}</div>
  </div>
</template>
<script>
import Api from '@/api';
import introduce from './introduce';
import courseList from '../components/e-course-list/e-course-list.vue';
import { formatFullTime } from '@/utils/date-toolkit.js';

export default {
  data() {
    return {
      user: null,
      vipInfo: null,
      vipData: {},
      levels: [{
        courses: {
          data: []
        },
        classrooms: {
          data: []
        }
      }],
      index: 0,
      vipLevelId: this.$router.query ? this.$router.query.vipLevelId : 1
    }
  },
  components: {
    'vip-introduce': introduce,
    'e-course-list': courseList
  },
  computed: {
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
      data.items = this.levels[this.index].courses.data;
      return data;
    },
    classroomData() {
      let data = {
        items: [],
        title: '会员班级',
        source: {},
        limit: 4
      }
      data.items = this.levels[this.index].classrooms.data;
      return data;
    },
    vipDeadline() {
      const time = new Date(this.vipInfo.deadline);
      return formatFullTime(time);
    },
    btnStatus() {
      const currentSeq = this.levels[this.index].seq;
      const userSeq = this.vipInfo.seq;
      console.log(userSeq,currentSeq,88)
      if (userSeq > currentSeq) return;
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
    })
  },
  methods: {
    activeIndex(index) {
      this.index = index;
    }
  }
}
</script>
